<template>
  <admin-layout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <CreateConsultantServiceForm 
        :consultants="consultants" 
        :categories="categories" 
        :tags="tags" />
  </admin-layout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import CreateConsultantServiceForm from '@/components/admin/consultantService/CreateConsultantService.vue'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { usePage } from '@inertiajs/vue3'


const { t } = useI18n()
const currentPageTitle = computed(() => t('consultant_services.addService'))

// استلام البيانات من Inertia
const consultants = computed(() => usePage().props.consultants)
const categories = computed(() => usePage().props.categories || [])
const tags = computed(() => usePage().props.tags || [])

</script>
