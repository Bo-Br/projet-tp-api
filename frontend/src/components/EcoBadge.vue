<script setup lang="ts">
import type { EcoMetrics } from '../types/devcoffee'
defineProps<{ metrics: EcoMetrics }>()
</script>

<template>
  <footer class="eco-footer" aria-label="Éco-score API">
    <strong>Éco-score</strong>
    <div class="eco-metric">
      <span>Temps</span>
      <b>{{ metrics.responseTime !== null ? `${metrics.responseTime} ms` : '—' }}</b>
    </div>
    <div class="eco-metric">
      <span>Payload</span>
      <b>{{ metrics.payloadSize !== null ? `${metrics.payloadSize} octets` : '—' }}</b>
    </div>
    <div class="eco-metric">
      <span>CO₂ estimé</span>
      <b>{{ metrics.co2 !== null ? `${metrics.co2} g` : '—' }}</b>
    </div>
  </footer>
</template>
