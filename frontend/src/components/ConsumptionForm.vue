<script setup lang="ts">
import { ref } from 'vue'
import type { Drink } from '../types/devcoffee'

defineProps<{
  drinks: Drink[]
  submitting: boolean
}>()

const emit = defineEmits<{
  (event: 'submit-consumption', payload: { developerName: string; drinkId: number }): void
}>()

const developerName = ref('')
const drinkId = ref<number | ''>('')

function handleSubmit(): void {
  const cleanName = developerName.value.trim()
  if (!cleanName || drinkId.value === '') return
  emit('submit-consumption', { developerName: cleanName, drinkId: Number(drinkId.value) })
}
</script>

<template>
  <section class="panel form-panel" aria-labelledby="record-heading">
    <h2 id="record-heading">Nouvelle consommation</h2>
    <form class="consumption-form" @submit.prevent="handleSubmit">
      <label for="developer-name">Développeur</label>
      <input
        id="developer-name"
        v-model="developerName"
        type="text"
        name="developerName"
        autocomplete="name"
        maxlength="100"
        placeholder="Nom du développeur"
        required
      />

      <label for="drink-id">Boisson</label>
      <select id="drink-id" v-model="drinkId" name="drinkId" required>
        <option value="" disabled>Choisir une boisson</option>
        <option v-for="drink in drinks" :key="drink.id" :value="drink.id">
          {{ drink.name }} · {{ drink.caffeineMg }} mg · {{ drink.volumeMl }} ml
        </option>
      </select>

      <button class="button button-primary" type="submit" :disabled="submitting || drinks.length === 0">
        {{ submitting ? 'Enregistrement…' : 'Enregistrer' }}
      </button>
      <p v-if="drinks.length === 0" class="field-hint">Aucune boisson disponible.</p>
    </form>
  </section>
</template>
