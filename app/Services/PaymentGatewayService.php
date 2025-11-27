<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Venta;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

date_default_timezone_set('America/La_Paz');

class PaymentGatewayService
{
    private $client_guzzle;
    private $url_login;
    private $url_list_methods;
    private $url_qr;
    private $url_query;
    private $clientCode;
    private $callbackUrl;
    private $tokenService;
    private $tokenSecret;
    private $paymentMethodId;
    private const CACHE_KEY_ACCESS_TOKEN = 'pagofacil_access_token';
    private const CACHE_KEY_TOKEN_EXPIRES = 'pagofacil_token_expires';
    private const CACHE_KEY_PAYMENT_METHOD_ID = 'pagofacil_payment_method_id';

    public function __construct()
    {
        $this->client_guzzle = new Client();
        $this->url_login = "https://masterqr.pagofacil.com.bo/api/services/v2/login";
        $this->url_list_methods = "https://masterqr.pagofacil.com.bo/api/services/v2/list-enabled-services";
        $this->url_qr = "https://masterqr.pagofacil.com.bo/api/services/v2/generate-qr";
        $this->url_query = "https://masterqr.pagofacil.com.bo/api/services/v2/query-transaction";

        // Obtener credenciales del .env
        $this->tokenService = env('PAGO_FACIL_TCTOKEN_SERVICE');
        $this->tokenSecret = env('PAGO_FACIL_TCTOKEN_SECRET');
        $this->clientCode = env('PAGO_FACIL_CLIENT_CODE', env('PAGO_FACIL_COMERCE_ID'));
        $this->callbackUrl = env('PAGO_FACIL_CALLBACK_URL', url('/payment/callback'));
    }

    /**
     * Autenticarse en la API de PagoFacil y obtener accessToken
     */
    private function authenticate()
    {
        try {
            if (!$this->tokenService || !$this->tokenSecret) {
                throw new \Exception('Credenciales de PagoFacil no configuradas. Verifica PAGO_FACIL_TCTOKEN_SERVICE y PAGO_FACIL_TCTOKEN_SECRET en .env');
            }

            $headers = [
                'Content-Type' => 'application/json',
                'tcTokenService' => $this->tokenService,
                'tcTokenSecret' => $this->tokenSecret
            ];

            Log::info('Autenticando en PagoFacil API', ['url' => $this->url_login]);

            $response = $this->client_guzzle->post($this->url_login, [
                'headers' => $headers
            ]);

            $result = json_decode($response->getBody()->getContents());

            if (isset($result->error) && $result->error == 1) {
                throw new \Exception('Error en autenticación: ' . ($result->message ?? 'Error desconocido'));
            }

            if (!isset($result->values->accessToken)) {
                throw new \Exception('No se recibió accessToken en la respuesta de autenticación');
            }

            $accessToken = $result->values->accessToken;
            $expiresInMinutes = $result->values->expiresInMinutes ?? 200;

            $cacheTime = (int) ($expiresInMinutes - 5) * 60;

            Cache::put(self::CACHE_KEY_ACCESS_TOKEN, $accessToken, $cacheTime);
            Cache::put(self::CACHE_KEY_TOKEN_EXPIRES, now()->addMinutes($expiresInMinutes), $cacheTime);

            Log::info('Autenticación exitosa en PagoFacil', [
                'expiresInMinutes' => $expiresInMinutes,
                'cacheTime' => $cacheTime
            ]);

            return $accessToken;

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Error en autenticación PagoFacil', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            throw new \Exception('Error al autenticarse en PagoFacil: ' . $e->getMessage());
        }
    }

    /**
     * Obtener accessToken válido (desde cache o renovarlo si es necesario)
     */
    private function getAccessToken()
    {
        $accessToken = Cache::get(self::CACHE_KEY_ACCESS_TOKEN);
        $expiresAt = Cache::get(self::CACHE_KEY_TOKEN_EXPIRES);

        if (!$accessToken || !$expiresAt || now()->greaterThan($expiresAt)) {
            Log::info('Token expirado o no existe, renovando autenticación');
            return $this->authenticate();
        }

        return $accessToken;
    }

    /**
     * Listar métodos de pago habilitados y obtener paymentMethodId
     */
    private function getPaymentMethodId()
    {
        $paymentMethodId = Cache::get(self::CACHE_KEY_PAYMENT_METHOD_ID);

        if ($paymentMethodId) {
            return $paymentMethodId;
        }

        try {
            $accessToken = $this->getAccessToken();

            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken
            ];

            Log::info('Listando métodos de pago habilitados', ['url' => $this->url_list_methods]);

            $response = $this->client_guzzle->get($this->url_list_methods, [
                'headers' => $headers
            ]);

            $result = json_decode($response->getBody()->getContents());

            if (isset($result->error) && $result->error == 1) {
                throw new \Exception('Error al listar métodos: ' . ($result->message ?? 'Error desconocido'));
            }

            $paymentMethodId = null;
            if (isset($result->values) && is_array($result->values)) {
                foreach ($result->values as $method) {
                    if (isset($method->paymentMethodName) &&
                        (stripos($method->paymentMethodName, 'QR') !== false)) {
                        $paymentMethodId = $method->paymentMethodId;
                        Log::info('Método QR encontrado', [
                            'paymentMethodId' => $paymentMethodId,
                            'paymentMethodName' => $method->paymentMethodName ?? null
                        ]);
                        break;
                    }
                }

                if (!$paymentMethodId && isset($result->values[0]->paymentMethodId)) {
                    $paymentMethodId = $result->values[0]->paymentMethodId;
                    Log::info('Usando primer método disponible', ['paymentMethodId' => $paymentMethodId]);
                }
            }

            if (!$paymentMethodId) {
                $paymentMethodId = env('PAGO_FACIL_PAYMENT_METHOD_ID', 4);
                Log::warning('No se encontró paymentMethodId en respuesta, usando valor por defecto', [
                    'paymentMethodId' => $paymentMethodId
                ]);
            }

            Cache::put(self::CACHE_KEY_PAYMENT_METHOD_ID, $paymentMethodId, 86400);

            return $paymentMethodId;

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $paymentMethodId = env('PAGO_FACIL_PAYMENT_METHOD_ID', 4);
            Log::warning('Usando paymentMethodId por defecto debido a error', [
                'paymentMethodId' => $paymentMethodId
            ]);
            return $paymentMethodId;
        }
    }

    /**
     * Procesar pago con QR usando PagoFácil
     */
    public function processQRPayment(Venta $venta, Pago $pago, $usuario)
    {
        DB::beginTransaction();
        try {
            // Generar número de pago único si no existe
            if (!$pago->nro_pago) {
                $pago->nro_pago = $this->generateNroPago();
            }

            // Preparar detalles del pago
            $detalles = $this->preparePaymentDetails($venta);

            // Generar QR
            $result = $this->generateQR($pago, $detalles, $usuario);

            // Verificar si hay error
            if (isset($result->error) && $result->error == 1) {
                DB::rollBack();
                throw new \Exception($result->message ?? 'Error al generar QR');
            }

            if (isset($result->success) && $result->success === false) {
                DB::rollBack();
                throw new \Exception($result->message ?? 'Error al generar QR');
            }

            $qrImage = null;
            $transactionId = null;

            // Extraer QR base64 según documentación
            if (isset($result->values->qrBase64)) {
                $qrImage = $result->values->qrBase64;
            } elseif (isset($result->qrBase64)) {
                $qrImage = $result->qrBase64;
            } elseif (isset($result->values->qrImage)) {
                $qrImage = $result->values->qrImage;
            } elseif (isset($result->data->qrBase64)) {
                $qrImage = $result->data->qrBase64;
            }

            if ($qrImage) {
                $binaryData = base64_decode($qrImage);
                if ($binaryData !== false && strlen($binaryData) > 0) {
                    $fileName = time() . '_' . $pago->nro_pago . '.png';
                    Storage::disk('public')->put('pagos/qr/' . $fileName, $binaryData, 'public');
                    $pago->qr_image = Storage::url('pagos/qr/' . $fileName);
                    Log::info('QR guardado exitosamente', ['file' => $fileName]);
                }
            }

            // Guardar transactionId (OBLIGATORIO para consultas posteriores según documentación)
            if (isset($result->values->transactionId)) {
                $transactionId = $result->values->transactionId;
                $pago->nro_transaccion = (string) $transactionId;
                Log::info('TransactionId guardado desde values.transactionId', [
                    'transactionId' => $transactionId,
                    'pago_id' => $pago->id
                ]);
            } elseif (isset($result->values->paymentMethodTransactionId)) {
                // Algunas APIs devuelven el ID con otro nombre
                $transactionId = $result->values->paymentMethodTransactionId;
                $pago->nro_transaccion = (string) $transactionId;
                Log::info('TransactionId guardado desde paymentMethodTransactionId', [
                    'transactionId' => $transactionId,
                    'pago_id' => $pago->id
                ]);
            } elseif (isset($result->transactionId)) {
                $transactionId = $result->transactionId;
                $pago->nro_transaccion = (string) $transactionId;
                Log::info('TransactionId guardado desde transactionId', [
                    'transactionId' => $transactionId,
                    'pago_id' => $pago->id
                ]);
            } else {
                Log::warning('No se recibió transactionId en la respuesta de generate-qr', [
                    'pago_id' => $pago->id,
                    'response' => $result
                ]);
            }

            // Guardar fecha de expiración
            if (isset($result->values->expirationDate)) {
                try {
                    $pago->qr_expires_at = \Carbon\Carbon::parse($result->values->expirationDate);
                } catch (\Exception $e) {
                    Log::warning('Error parseando expirationDate', ['date' => $result->values->expirationDate]);
                }
            }

            $pago->estado = 'PENDIENTE';
            $pago->save();

            DB::commit();

            return [
                'pago' => $pago,
                'result' => $result,
                'qrBase64' => $qrImage,
                'message' => 'QR generado exitosamente'
            ];

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error en PaymentGatewayService (QR): ' . $th->getMessage());
            throw $th;
        }
    }

    /**
     * Confirmar pago desde callback
     */
    public function confirmPayment($nroPago)
    {
        $pago = Pago::where('nro_pago', $nroPago)
                    ->orWhere('nro_pago', (string) $nroPago)
                    ->with(['venta'])
                    ->first();

        if (!$pago) {
            Log::warning('Pago no encontrado en callback', ['nro_pago' => $nroPago]);
            throw new \Exception('Pago no encontrado con número: ' . $nroPago);
        }

        DB::beginTransaction();
        try {
            $pago->estado = 'PAGADO';
            $pago->fecha_confirmacion = now();
            if (!$pago->fecha_pago) {
                $pago->fecha_pago = now();
            }
            $pago->save();

            $venta = $pago->venta;
            if ($venta) {
                // Si es pago único (contado) o todas las cuotas están pagadas, confirmar venta
                if ($pago->tipo === 'CONTADO') {
                    $venta->estado = true;
                    $venta->save();
                } elseif ($pago->tipo === 'CUOTA') {
                    // Verificar si todas las cuotas están pagadas
                    $pagosPendientes = $venta->pagos()->where('estado', '!=', 'PAGADO')->count();
                    if ($pagosPendientes === 0) {
                        $venta->estado = true;
                        $venta->save();
                    }
                }
            }

            DB::commit();

            return $pago;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error confirmando pago: ' . $th->getMessage());
            throw $th;
        }
    }

    /**
     * Consultar estado de pago manualmente
     * Según documentación: debe enviar pagofacilTransactionId O companyTransactionId
     */
    public function consultPaymentStatus(Pago $pago)
    {
        try {
            // Recargar el pago para asegurar que tenemos los datos más recientes
            $pago->refresh();

            // Validar que tenemos al menos uno de los IDs necesarios
            if (!$pago->nro_transaccion && !$pago->nro_pago) {
                Log::warning('No se puede consultar estado: falta nro_transaccion y nro_pago', [
                    'pago_id' => $pago->id
                ]);
                throw new \Exception('No se puede consultar el estado: falta información de transacción');
            }

            $accessToken = $this->getAccessToken();

            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken
            ];

            // Según documentación: priorizar pagofacilTransactionId (transactionId de PagoFácil)
            // Si no existe, usar companyTransactionId (nro_pago interno)
            $body = [];

            if ($pago->nro_transaccion) {
                // Usar el transactionId de PagoFácil (recomendado según documentación)
                $body['pagofacilTransactionId'] = (string) $pago->nro_transaccion;
                Log::info('Consultando con pagofacilTransactionId', [
                    'transactionId' => $pago->nro_transaccion
                ]);
            } elseif ($pago->nro_pago) {
                // Fallback: usar el paymentNumber interno
                $body['companyTransactionId'] = (string) $pago->nro_pago;
                Log::info('Consultando con companyTransactionId', [
                    'nro_pago' => $pago->nro_pago
                ]);
            }

            Log::info('Consultando estado de pago', [
                'url' => $this->url_query,
                'body' => $body,
                'pago_id' => $pago->id,
                'nro_transaccion' => $pago->nro_transaccion,
                'nro_pago' => $pago->nro_pago
            ]);

            $response = $this->client_guzzle->post($this->url_query, [
                'headers' => $headers,
                'json' => $body
            ]);

            $result = json_decode($response->getBody()->getContents());

            Log::info('Respuesta de consulta de estado', ['result' => $result]);

            $paymentInfo = null;

            if (isset($result->values)) {
                $values = $result->values;
                $paymentStatus = $values->paymentStatus ?? null;

                // Si el pago está completado (status = 1) y aún no está confirmado
                if ($paymentStatus == 1 && $pago->estado !== 'PAGADO') {
                    Log::info('Pago confirmado desde consulta manual', [
                        'nro_pago' => $pago->nro_pago,
                        'paymentStatus' => $paymentStatus
                    ]);
                    $this->confirmPayment($pago->nro_pago);
                }

                // Actualizar información adicional
                if (isset($values->amount)) {
                    $pago->monto = $values->amount;
                }
                if (isset($values->paymentDate) && isset($values->paymentTime)) {
                    try {
                        $pago->fecha_confirmacion = \Carbon\Carbon::parse(
                            $values->paymentDate . ' ' . $values->paymentTime
                        );
                    } catch (\Exception $e) {
                        Log::warning('Error parseando fecha de pago', [
                            'date' => $values->paymentDate,
                            'time' => $values->paymentTime
                        ]);
                    }
                }
                $pago->save();

                $paymentInfo = [
                    'paymentStatus' => $paymentStatus,
                    'paymentStatusDescription' => $values->paymentStatusDescription ?? null,
                    'amount' => $values->amount ?? null,
                    'currencyCode' => $values->currencyCode ?? 'BOB',
                    'paymentDate' => $values->paymentDate ?? null,
                    'paymentTime' => $values->paymentTime ?? null,
                ];
            }

            return [
                'result' => $result,
                'paymentInfo' => $paymentInfo
            ];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorResponse = null;
            if ($e->hasResponse()) {
                $errorResponse = $e->getResponse()->getBody()->getContents();
                Log::error('Error HTTP consultando estado de pago', [
                    'status' => $e->getResponse()->getStatusCode(),
                    'response' => $errorResponse,
                    'pago_id' => $pago->id ?? null,
                    'nro_transaccion' => $pago->nro_transaccion ?? null,
                    'nro_pago' => $pago->nro_pago ?? null
                ]);
            }
            Log::error('Error consultando estado de pago (RequestException): ' . $e->getMessage(), [
                'pago_id' => $pago->id ?? null,
                'nro_transaccion' => $pago->nro_transaccion ?? null,
                'nro_pago' => $pago->nro_pago ?? null
            ]);
            return [
                'result' => null,
                'paymentInfo' => null,
                'error' => $e->getMessage(),
                'errorResponse' => $errorResponse
            ];
        } catch (\Throwable $th) {
            Log::error('Error consultando estado de pago: ' . $th->getMessage(), [
                'pago_id' => $pago->id ?? null,
                'trace' => $th->getTraceAsString()
            ]);
            return [
                'result' => null,
                'paymentInfo' => null,
                'error' => $th->getMessage()
            ];
        }
    }

    /**
     * Generar QR en la pasarela
     */
    private function generateQR(Pago $pago, $detalles, $usuario)
    {
        $orderDetail = [];
        $serial = 1;
        foreach ($detalles as $detalle) {
            $orderDetail[] = [
                "serial" => $serial++,
                "product" => $detalle['producto'],
                "quantity" => $detalle['cantidad'],
                "price" => $detalle['precio'],
                "discount" => 0,
                "total" => $detalle['total']
            ];
        }

        $accessToken = $this->getAccessToken();
        $paymentMethodId = $this->getPaymentMethodId();

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken
        ];

        $body = [
            "paymentMethod" => $paymentMethodId,
            "clientName" => $usuario->nombre . ' ' . $usuario->apellido,
            "documentType" => 1, // 1 = CI
            "documentId" => $usuario->email ?? '0000000',
            "phoneNumber" => $usuario->telefono ?? '',
            "email" => $usuario->email ?? '',
            "paymentNumber" => (string) $pago->nro_pago,
            "amount" => (float) $pago->monto,
            "currency" => 2, // 2 = Bs (Bolivianos)
            "clientCode" => $this->clientCode,
            "callbackUrl" => $this->callbackUrl,
            "orderDetail" => $orderDetail
        ];

        Log::info('Generando QR con PagoFácil', [
            'url' => $this->url_qr,
            'paymentNumber' => $pago->nro_pago,
            'amount' => $pago->monto,
            'callbackUrl' => $this->callbackUrl
        ]);

        try {
            $response = $this->client_guzzle->post($this->url_qr, [
                'headers' => $headers,
                'json' => $body
            ]);

            $responseBody = $response->getBody()->getContents();
            $result = json_decode($responseBody);

            Log::info('Respuesta de API QR', [
                'status' => $response->getStatusCode(),
                'response' => $result
            ]);

            return $result;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Error en petición a API QR', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            throw new \Exception('Error al comunicarse con la pasarela de pagos: ' . $e->getMessage());
        }
    }

    /**
     * Preparar detalles del pago desde la venta
     */
    private function preparePaymentDetails(Venta $venta)
    {
        $detalles = [];

        if ($venta->detalles && $venta->detalles->count() > 0) {
            foreach ($venta->detalles as $detalle) {
                $productoNombre = 'Producto';
                if ($detalle->producto_id) {
                    $producto = \App\Models\Producto::find($detalle->producto_id);
                    $productoNombre = $producto ? $producto->nombre : 'Producto';
                } elseif ($detalle->servicio_id) {
                    $servicio = \App\Models\Servicio::find($detalle->servicio_id);
                    $productoNombre = $servicio ? $servicio->nombre : 'Servicio';
                }

                $detalles[] = [
                    'producto' => $productoNombre,
                    'cantidad' => $detalle->cantidad,
                    'precio' => $detalle->precio_unitario,
                    'total' => $detalle->importe_total
                ];
            }
        } else {
            $detalles[] = [
                'producto' => 'Pago de Cuota',
                'cantidad' => 1,
                'precio' => $venta->importe_total,
                'total' => $venta->importe_total
            ];
        }

        return $detalles;
    }

    /**
     * Generar número de pago único
     */
    private function generateNroPago()
    {
        do {
            $nroPago = (string) rand(188888889, 999999999);
        } while (Pago::where('nro_pago', $nroPago)->exists());

        return $nroPago;
    }
}

