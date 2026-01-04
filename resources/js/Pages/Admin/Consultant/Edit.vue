<template>
  <admin-layout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <EditConsultantForm
      :consultant="consultant"
      :governorates="governorates"
      :districts="districts"
      :areas="areas"
      :users="users"
    />
  </admin-layout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import EditConsultantForm from '@/components/admin/consultant/EditConsultant.vue'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { usePage } from '@inertiajs/vue3'


const { t } = useI18n()
const currentPageTitle = computed(() => t('consultants.editConsultant'))

// استلام البيانات من Inertia
const consultant = computed(() => usePage().props.consultant)
const governorates = computed(() => usePage().props.governorates)
const districts = computed(() => usePage().props.districts)
const areas = computed(() => usePage().props.areas)
const users = computed(() => usePage().props.users)


</script>
