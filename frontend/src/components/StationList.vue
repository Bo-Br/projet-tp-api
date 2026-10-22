<template>
<section class="card">
<h2>Stations disponibles</h2>
<ul class="list">
<li v-for="st in stations" :key="st.id" class="list-item">
<div>
<strong>{{ st.locationName }}</strong>
<span class="badge">{{ st.powerKw }} kW</span>
</div>
<span :class="st.isAvailable ? 'status-online' : 'status-offline'">
{{ st.isAvailable ? 'Disponible' : 'Occupée' }}
</span>
</li>
</ul>
</section>
</template>
<script setup lang="ts">
import type { Station } from '../types/ecopark';

defineProps<{
stations: Station[];
}>();
</script>
<style scoped>
.card { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding:
20px; }
.list { list-style: none; padding: 0; }
.list-item { display: flex; justify-content: space-between; align-items: center; padding:
8px 0; border-bottom: 1px solid #dee2e6; }
.badge { background: #e2e3e5; padding: 2px 6px; border-radius: 4px; font-size: 12px;
margin-left: 8px; }
.status-online { color: #198754; font-weight: bold; font-size: 13px; }
.status-offline { color: #dc3545; font-weight: bold; font-size: 13px; }
</style>            