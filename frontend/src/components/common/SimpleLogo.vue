<template>
  <div class="simple-logo" :class="sizeClass">
    <div class="logo-icon">
      <i class="fas fa-seedling"></i>
      <i class="fas fa-cow"></i>
    </div>
    <div class="logo-text">
      <span class="agro">Agro</span><span class="sistemas">Sistemas</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  size?: 'small' | 'medium' | 'large'
}

const props = withDefaults(defineProps<Props>(), {
  size: 'medium'
})

const sizeClass = computed(() => `logo-${props.size}`)
</script>

<style scoped>
.simple-logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.logo-icon {
  position: relative;
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

.logo-icon i.fa-seedling {
  position: relative;
  z-index: 2;
}

.logo-icon i.fa-cow {
  position: absolute;
  bottom: 2px;
  right: 2px;
  background: rgba(0, 0, 0, 0.25);
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-text {
  font-weight: 700;
  line-height: 1;
  white-space: nowrap;
}

.agro {
  color: #16a34a;
}

.sistemas {
  color: #64748b;
}

/* Small */
.logo-small .logo-icon {
  width: 40px;
  height: 40px;
}

.logo-small .logo-icon i.fa-seedling {
  font-size: 1.125rem;
}

.logo-small .logo-icon i.fa-cow {
  font-size: 0.5rem;
  width: 16px;
  height: 16px;
}

.logo-small .logo-text {
  font-size: 1.25rem;
}

/* Medium */
.logo-medium .logo-icon {
  width: 50px;
  height: 50px;
}

.logo-medium .logo-icon i.fa-seedling {
  font-size: 1.5rem;
}

.logo-medium .logo-icon i.fa-cow {
  font-size: 0.625rem;
  width: 20px;
  height: 20px;
}

.logo-medium .logo-text {
  font-size: 1.5rem;
}

/* Large */
.logo-large .logo-icon {
  width: 70px;
  height: 70px;
}

.logo-large .logo-icon i.fa-seedling {
  font-size: 2rem;
}

.logo-large .logo-icon i.fa-cow {
  font-size: 0.875rem;
  width: 28px;
  height: 28px;
}

.logo-large .logo-text {
  font-size: 2rem;
}
</style>
