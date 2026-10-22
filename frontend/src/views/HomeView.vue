<script setup lang="ts">
import { onMounted } from 'vue'
import ConsumptionForm from '../components/ConsumptionForm.vue'
import ConsumptionHistory from '../components/ConsumptionHistory.vue'
import EcoBadge from '../components/EcoBadge.vue'
import { useDevCoffee } from '../composables/useDevCoffee'
import type { Consumption } from '../types/devcoffee'

const {
  drinks,
  consumptions,
  loading,
  submitting,
  deletingId,
  errorTitle,
  errorMessage,
  successMessage,
  ecoMetrics,
  refreshAll,
  recordConsumption,
  deleteConsumption,
  clearAlerts,
} = useDevCoffee()

onMounted(refreshAll)

async function handleRecord(payload: { developerName: string; drinkId: number }): Promise<void> {
  await recordConsumption(payload)
}

async function handleDelete(item: Consumption): Promise<void> {
  await deleteConsumption(item)
}
</script>

<template>
  <main class="page-shell">
    <header class="site-header">
      <h1>DevCoffee</h1>
    </header>

    <section class="notice-stack" aria-live="polite">
      <div v-if="errorTitle" class="alert alert-error" role="alert">
        <div class="alert-copy">
          <strong>{{ errorTitle }}</strong>
          <p>{{ errorMessage }}</p>
        </div>
        <button class="alert-close" type="button" aria-label="Fermer" @click="clearAlerts">×</button>
      </div>
      <div v-if="successMessage" class="alert alert-success" role="status">
        <p>{{ successMessage }}</p>
        <button class="alert-close" type="button" aria-label="Fermer" @click="clearAlerts">×</button>
      </div>
    </section>

    <div class="content-grid">
      <ConsumptionForm :drinks="drinks" :submitting="submitting" @submit-consumption="handleRecord" />
      <ConsumptionHistory
        :consumptions="consumptions"
        :deleting-id="deletingId"
        :loading="loading"
        @delete-consumption="handleDelete"
      />
    </div>

    <EcoBadge :metrics="ecoMetrics" />
  </main>
</template>
