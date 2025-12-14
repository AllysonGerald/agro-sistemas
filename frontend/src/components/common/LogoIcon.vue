<template>
  <div class="logo-icon" :class="[sizeClass, variantClass]">
    <div class="logo-circle">
      <i class="fas fa-seedling plant-icon"></i>
      <i class="fas fa-cow cow-badge"></i>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  size?: 'small' | 'medium' | 'large'
  variant?: 'default' | 'sidebar' | 'login'
}

const props = withDefaults(defineProps<Props>(), {
  size: 'medium',
  variant: 'default'
})

const sizeClass = computed(() => `logo-icon-${props.size}`)
const variantClass = computed(() => `logo-icon-${props.variant}`)
</script>

<style scoped>
.logo-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.logo-circle {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.plant-icon {
  position: relative;
  z-index: 2;
}

.cow-badge {
  position: absolute;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Variant Default - Padrão igual à imagem de referência */
.logo-icon-default .logo-circle {
  background: #16a34a;
  border: 3px solid rgba(255, 255, 255, 0.4);
  box-shadow: 0 8px 24px rgba(22, 163, 74, 0.4), inset 0 0 0 2px rgba(255, 255, 255, 0.15);
  position: relative;
}

.logo-icon-default .plant-icon {
  color: white;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.logo-icon-default .cow-badge {
  background: rgba(0, 0, 0, 0.25);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.5);
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

/* Variant Sidebar - Igual à segunda imagem */
.logo-icon-sidebar .logo-circle {
  background: #16a34a;
  border: 3px solid rgba(255, 255, 255, 0.4);
  box-shadow: 0 8px 24px rgba(22, 163, 74, 0.4), inset 0 0 0 2px rgba(255, 255, 255, 0.15);
  position: relative;
}

.logo-icon-sidebar .plant-icon {
  color: white;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.logo-icon-sidebar .cow-badge {
  background: rgba(0, 0, 0, 0.25);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.5);
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

/* Variant Login - Padrão igual à imagem de referência */
.logo-icon-login .logo-circle {
  background: #16a34a;
  border: 3px solid rgba(255, 255, 255, 0.4);
  box-shadow: 0 8px 24px rgba(22, 163, 74, 0.4), inset 0 0 0 2px rgba(255, 255, 255, 0.15);
  position: relative;
}

.logo-icon-login .plant-icon {
  color: white;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.logo-icon-login .cow-badge {
  background: rgba(0, 0, 0, 0.25);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.5);
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

/* Small Size */
.logo-icon-small .logo-circle {
  width: 48px;
  height: 48px;
}

.logo-icon-small .plant-icon {
  font-size: 1.5rem;
}

.logo-icon-small .cow-badge {
  bottom: 2px;
  right: 2px;
  width: 20px;
  height: 20px;
  font-size: 0.65rem;
}

/* Medium Size */
.logo-icon-medium .logo-circle {
  width: 80px;
  height: 80px;
}

.logo-icon-medium .plant-icon {
  font-size: 2rem;
}

.logo-icon-medium .cow-badge {
  bottom: -4px;
  right: -4px;
  width: 32px;
  height: 32px;
  font-size: 0.875rem;
}

/* Large Size */
.logo-icon-large .logo-circle {
  width: 120px;
  height: 120px;
}

.logo-icon-large .plant-icon {
  font-size: 3rem;
}

.logo-icon-large .cow-badge {
  bottom: -6px;
  right: -6px;
  width: 48px;
  height: 48px;
  font-size: 1.25rem;
}
</style>

