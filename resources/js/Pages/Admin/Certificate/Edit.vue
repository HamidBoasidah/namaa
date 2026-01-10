<template>
  <admin-layout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <EditCertificateForm
      :certificate="certificate"
      :consultants="consultants"
    />
  </admin-layout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import EditCertificateForm from '@/components/admin/certificate/EditCertificate.vue'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { usePage } from '@inertiajs/vue3'

const { t } = useI18n()
const currentPageTitle = computed(() => t('certificate.editCertificate'))

const certificate = computed(() => usePage().props.certificate)
const consultants = computed(() => usePage().props.consultants ?? [])
</script>
