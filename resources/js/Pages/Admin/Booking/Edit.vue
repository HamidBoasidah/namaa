<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />
    <EditBookingForm
      :booking="booking"
      :consultants="consultants"
      :clients="clients"
      :services="services"
    />
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import EditBookingForm from '@/components/admin/booking/EditBooking.vue'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { usePage } from '@inertiajs/vue3'

const { t } = useI18n()
const currentPageTitle = computed(() => t('bookings.editBooking'))

const booking = computed(() => usePage().props.booking)
const consultants = computed(() => usePage().props.consultants)
const clients = computed(() => usePage().props.clients)
const services = computed(() => usePage().props.services)
</script>
