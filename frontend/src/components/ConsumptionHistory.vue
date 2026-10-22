<script setup lang="ts">
import type { Consumption } from '../types/devcoffee'

defineProps<{
  consumptions: Consumption[]
  deletingId: number | null
  loading: boolean
}>()

const emit = defineEmits<{
  (event: 'delete-consumption', item: Consumption): void
}>()

function formatDate(value: string): string {
  const parsed = new Date(value.replace(' ', 'T'))
  if (Number.isNaN(parsed.getTime())) return value
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'short', timeStyle: 'short' }).format(parsed)
}
</script>

<template>
  <section class="panel history-panel" aria-labelledby="history-heading">
    <h2 id="history-heading">Consommations récentes</h2>

    <p v-if="loading && consumptions.length === 0" class="empty-state">Chargement…</p>
    <p v-else-if="consumptions.length === 0" class="empty-state">Aucune consommation.</p>
    <div v-else class="table-wrap">
      <table>
        <thead>
          <tr>
            <th scope="col">Développeur</th>
            <th scope="col">Boisson</th>
            <th scope="col">Caféine</th>
            <th scope="col">Date</th>
            <th scope="col"><span class="sr-only">Action</span></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in consumptions" :key="item.id">
            <td>{{ item.developerName }}</td>
            <td>{{ item.drinkName }}</td>
            <td>{{ item.caffeineMg }} mg</td>
            <td class="date-cell">{{ formatDate(item.consumedAt) }}</td>
            <td class="action-cell">
              <button
                class="delete-button"
                type="button"
                :aria-label="`Supprimer la consommation de ${item.developerName}`"
                :disabled="deletingId === item.id"
                @click="emit('delete-consumption', item)"
              >
                {{ deletingId === item.id ? '…' : 'Supprimer' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>
