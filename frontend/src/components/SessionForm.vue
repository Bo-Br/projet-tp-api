<template>
<section class="card">
<h2>Démarrer une recharge</h2>
<form @submit.prevent="handleSubmit">
<div class="form-group">
<label>Utilisateur :</label>
<input v-model="userName" placeholder="Ex: Clara" required />
</div>
<div class="form-group">
<label>Station :</label>
<select v-model="stationId" required>
<option value="" disabled>-- Sélectionner une borne --</option>
<option v-for="st in stations" :key="st.id" :value="st.id">
{{ st.locationName }} ({{ st.powerKw }} kW)
</option>
</select>
</div>
<div class="form-group">
<label>Puissance demandée (kWh) :</label>
<input v-model.number="kwhRequested" type="number" required />
</div>
<button type="submit" class="btn-primary">Lancer la recharge</button>
</form>
</section>
</template>
<script setup lang="ts">
import { ref } from 'vue';
import type { Station } from '../types/ecopark';
const props = defineProps<{
stations: Station[];
}>();
const emit = defineEmits<{
(e: 'submit-session', payload: { userName: string; stationId: number; kwhRequested:
number }): void;
}>();
const userName = ref('');
const stationId = ref<number | ''>('');
const kwhRequested = ref(20);
const handleSubmit = () => {
if (stationId.value !== '') {
emit('submit-session', {
userName: userName.value,
stationId: Number(stationId.value),
kwhRequested: kwhRequested.value,
});
stationId.value = '';
}
};
</script>
<style scoped>
.card { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding:
20px; }
.form-group { margin-bottom: 15px; display: flex; flex-direction: column; gap: 5px; }
input, select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;
}
.btn-primary { background: #198754; color: white; border: none; padding: 10px;
border-radius: 4px; cursor: pointer; width: 100%; }
</style>    