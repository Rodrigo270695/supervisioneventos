<template>
  <div>
    <div v-if="times && times.length > 0">
      <div v-for="time in times" :key="time.id">
        <!-- Agregar validación antes de acceder a name -->
        <div>{{ time?.timeType?.name || 'Sin tipo' }}</div>
        <!-- Resto del contenido -->
      </div>
    </div>
    <div v-else>
      No hay tiempos registrados
    </div>
  </div>
</template>

<script setup>
defineProps({
  times: {
    type: Array,
    required: true,
    default: () => []
  },
  timeTypes: {
    type: Array,
    required: true,
    default: () => []
  },
  eventId: {
    type: [Number, String],
    required: true
  }
})
</script>
