<template>
    <div class="custom-pagination">
        <div class="pagination-info">
            <span class="pagination-text">
                {{ firstItem }} a {{ lastItem }} de {{ total }} registros
            </span>
        </div>

        <div class="pagination-controls">
            <button class="pagination-btn" :class="{ disabled: currentPage === 1 }" :disabled="currentPage === 1"
                @click="goToPage(1)" title="Primeira página">
                <i class="pi pi-angle-double-left"></i>
            </button>

            <button class="pagination-btn" :class="{ disabled: currentPage === 1 }" :disabled="currentPage === 1"
                @click="goToPage(currentPage - 1)" title="Página anterior">
                <i class="pi pi-angle-left"></i>
            </button>

            <div class="pagination-pages">
                <button v-for="page in visiblePages" :key="page" class="pagination-page"
                    :class="{ active: page === currentPage }" @click="goToPage(page)">
                    {{ page }}
                </button>
            </div>

            <button class="pagination-btn" :class="{ disabled: currentPage === totalPages }"
                :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)" title="Próxima página">
                <i class="pi pi-angle-right"></i>
            </button>

            <button class="pagination-btn" :class="{ disabled: currentPage === totalPages }"
                :disabled="currentPage === totalPages" @click="goToPage(totalPages)" title="Última página">
                <i class="pi pi-angle-double-right"></i>
            </button>
        </div>

        <div class="pagination-per-page">
            <label for="per-page-select">Por página:</label>
            <select id="per-page-select" :value="perPage" @change="changePerPage" class="per-page-select">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
    currentPage: number
    totalPages: number
    total: number
    perPage: number
}

const props = defineProps<Props>()

const emit = defineEmits<{
    pageChange: [page: number]
    perPageChange: [perPage: number]
}>()

const firstItem = computed(() => {
    return props.total === 0 ? 0 : (props.currentPage - 1) * props.perPage + 1
})

const lastItem = computed(() => {
    const last = props.currentPage * props.perPage
    return Math.min(last, props.total)
})

const visiblePages = computed(() => {
    const pages = []
    const start = Math.max(1, props.currentPage - 2)
    const end = Math.min(props.totalPages, props.currentPage + 2)

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    return pages
})

const goToPage = (page: number) => {
    console.log('CustomPagination - goToPage chamado:', page, 'currentPage:', props.currentPage, 'totalPages:', props.totalPages)
    if (page >= 1 && page <= props.totalPages && page !== props.currentPage) {
        console.log('CustomPagination - emitindo pageChange:', page)
        emit('pageChange', page)
    } else {
        console.log('CustomPagination - página ignorada:', page)
    }
}

const changePerPage = (event: Event) => {
    const target = event.target as HTMLSelectElement
    emit('perPageChange', parseInt(target.value))
}
</script>

<style scoped>
.custom-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: white;
    border-top: 1px solid #e5e7eb;
    gap: 1rem;
    flex-wrap: wrap;
}

.pagination-info {
    display: flex;
    align-items: center;
}

.pagination-text {
    color: #6b7280;
    font-size: 0.875rem;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.pagination-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border: 1px solid #d1d5db;
    background: white;
    color: #374151;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;
}

.pagination-btn:hover:not(.disabled) {
    background: #d1fae5;
    border-color: #10b981;
    color: #10b981;
}

.pagination-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-pages {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin: 0 0.5rem;
}

.pagination-page {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 2rem;
    height: 2rem;
    padding: 0 0.5rem;
    border: 1px solid #d1d5db;
    background: white;
    color: #374151;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.pagination-page:hover {
    background: #d1fae5;
    border-color: #10b981;
    color: #10b981;
}

.pagination-page.active {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.pagination-per-page {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pagination-per-page label {
    color: #6b7280;
    font-size: 0.875rem;
}

.per-page-select {
    padding: 0.25rem 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background: white;
    color: #374151;
    font-size: 0.875rem;
    cursor: pointer;
}

.per-page-select:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.per-page-select:hover {
    border-color: #10b981;
}

@media (max-width: 768px) {
    .custom-pagination {
        flex-direction: column;
        gap: 0.75rem;
    }

    .pagination-controls {
        order: 1;
    }

    .pagination-info {
        order: 2;
    }

    .pagination-per-page {
        order: 3;
    }
}
</style>
