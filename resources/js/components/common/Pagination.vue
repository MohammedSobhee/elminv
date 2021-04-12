<template>
    <div>
        <nav aria-label="Pagination">
            <ul class="pagination justify-content-start">
                <li class="page-item" :class="{disabled:isInFirstPage}">
                    <button
                        type="button"
                        class="page-link"
                        tabindex="-1"
                        :disabled="isInFirstPage"
                        @click="onClickFirstPage">
                        First
                    </button>
                </li>
                <li class="page-item" :class="{disabled:isInFirstPage}">
                    <button
                        type="button"
                        class="page-link"
                        tabindex="-1"
                        :disabled="isInFirstPage"
                        @click="onClickPreviousPage">
                        Previous
                    </button>
                </li>
                <li
                    v-for="page in pages"
                    :key="page.name"
                    :class="{active:isPageActive(page.name)}"
                    class="page-item"
                    @click="onClickPage(page.name)">
                    <button
                        :disabled="page.isDisabled"
                        type="button"
                        class="page-link">
                        {{ page.name }}
                    </button>
                </li>
                <li class="page-item" :class="{disabled:isInLastPage}">
                    <button
                        type="button"
                        class="page-link"
                        :disabled="isInLastPage"
                        @click="onClickNextPage">
                        Next
                    </button>
                </li>
                <li class="page-item" :class="{disabled:isInLastPage}">
                    <button
                        type="button"
                        class="page-link"
                        :disabled="isInLastPage"
                        @click="onClickLastPage">
                        Last
                    </button>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
export default {
    name: 'GradingItem',
    props: {
        resultsPerPage: {
            type: Number,
            required: true
        },
        total: {
            type: Number,
            required: true
        },
        currentPage: {
            type: Number,
            required: true
        }
    },

    computed: {
        totalPages() {
            return Math.ceil(this.total / this.resultsPerPage);
        },

        isInFirstPage() {
            return this.currentPage === 1;
        },

        isInLastPage() {
            return this.currentPage === this.totalPages;
        },

        pages() {
            const range = [];
            for (let i = 1; i <= this.totalPages; i += 1) {
                range.push({
                    name: i,
                    isDisabled: i === this.currentPage
                });
            }
            return range;
        }
    },

    methods: {
        isPageActive(page) {
            return this.currentPage === page;
        },
        onClickFirstPage() {
            this.$emit('pagechanged', 1);
        },
        onClickPreviousPage() {
            this.$emit('pagechanged', this.currentPage - 1);
        },
        onClickPage(page) {
            this.$emit('pagechanged', page);
        },
        onClickNextPage() {
            this.$emit('pagechanged', this.currentPage + 1);
        },
        onClickLastPage() {
            this.$emit('pagechanged', this.totalPages);
        }
    }
};
</script>
