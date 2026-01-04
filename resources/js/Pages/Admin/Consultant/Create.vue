<template>
  <admin-layout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <AddConsultantForm 
    :users="users"
      :governorates="governorates"
      :districts="districts"
      :areas="areas" />
  </admin-layout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import AddConsultantForm from '@/components/admin/consultant/CreateConsultant.vue'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { usePage } from '@inertiajs/vue3'


const { t } = useI18n()
const currentPageTitle = computed(() => t('consultants.addConsultant'))

// استلام البيانات من Inertia

const users = computed(() => usePage().props.users)
const governorates = computed(() => usePage().props.governorates)
const districts = computed(() => usePage().props.districts)
const areas = computed(() => usePage().props.areas)

</script>
