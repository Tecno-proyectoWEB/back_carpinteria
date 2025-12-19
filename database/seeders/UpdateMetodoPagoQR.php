<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MetodoPago;
use Illuminate\Support\Facades\Log;

class UpdateMetodoPagoQR extends Seeder
{
    /**
     * Actualizar el método de pago QR para que sea reconocido como electrónico.
     */
    public function run(): void
    {
        try {
            $metodoPagoQR = MetodoPago::where('nombre', 'QR')->first();
            
            if ($metodoPagoQR) {
                $metodoPagoQR->update([
                    'es_electronico' => true,
                    'tipo_electronico' => 'QR',
                    'activo' => true,
                ]);
                
                Log::info('Método de pago QR actualizado correctamente', [
                    'id' => $metodoPagoQR->id,
                    'nombre' => $metodoPagoQR->nombre,
                    'es_electronico' => $metodoPagoQR->es_electronico,
                    'tipo_electronico' => $metodoPagoQR->tipo_electronico,
                ]);
                
                $this->command->info('✅ Método de pago QR actualizado exitosamente');
            } else {
                // Si no existe, crearlo
                $metodoPagoQR = MetodoPago::create([
                    'nombre' => 'QR',
                    'descripcion' => 'Pago mediante código QR con PagoFácil',
                    'es_electronico' => true,
                    'tipo_electronico' => 'QR',
                    'activo' => true,
                ]);
                
                $this->command->info('✅ Método de pago QR creado exitosamente');
            }
        } catch (\Exception $e) {
            Log::error('Error al actualizar método de pago QR: ' . $e->getMessage());
            $this->command->error('❌ Error: ' . $e->getMessage());
        }
    }
}
