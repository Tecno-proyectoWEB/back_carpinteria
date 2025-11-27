<template>
    <Layout :auth="auth">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Estado del Pago</h1>

            <div class="bg-white rounded-lg shadow p-6">
                <PaymentModal
                    :show="true"
                    :pago="pago"
                    :payment-info="paymentInfo"
                    @close="cerrar"
                />
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { route } from '../../ziggy.js'
import Layout from '../Layout.vue'
import PaymentModal from '../../Components/PaymentModal.vue'

const props = defineProps({
    auth: Object,
    pago: Object,
    paymentInfo: Object,
})

const cerrar = () => {
    if (props.pago && props.pago.venta_id) {
        router.visit(route('ventas.show', props.pago.venta_id))
    } else {
        router.visit(route('ventas.index'))
    }
}
</script>

