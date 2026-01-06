<template>
  <admin-layout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <EditConsultantServiceForm
      :service="service"
      :consultants="consultants"
      :categories="categories"
      :tags="tags"
    />
  </admin-layout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import EditConsultantServiceForm from '@/components/admin/consultantService/EditConsultantService.vue'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { usePage } from '@inertiajs/vue3'


const { t } = useI18n()
const currentPageTitle = computed(() => t('consultant_services.editService'))

// استلام البيانات من Inertia
const service = computed(() => usePage().props.service)
const consultants = computed(() => usePage().props.consultants)
const categories = computed(() => usePage().props.categories || [])
const tags = computed(() => usePage().props.tags || [])

</script>
