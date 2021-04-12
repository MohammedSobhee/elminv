<template>
<div>
    <h4 v-if="dataOrders.length > 0" class="mt-4">
        Recent Shopify Orders:
    </h4>
    <div v-for="(order, index) in dataOrders" :key="index">
        <!-- <pre>{{ orders }}</pre> -->
        <div class="card" :class="{'mt-5': index !== 0 }">
            <div class="card-body">
                <div class="d-flex">
                    <strong class="card-title text-primary flex-grow-1 mb-3">
                        Order # {{ order.id }} from <a :href="`mailto:${order.email}`">{{ order.email }}</a>
                    </strong>
                    <strong class="pr-3">Total: <span class="text-secondary">{{ order.total_price }}</span></strong>
                </div>

                <ul class="list-group-flush">
                    <li v-for="line in order.line_items" :key="line.id" class="list-group-item d-flex">
                        <div class="flex-grow-1">
                            {{ line.title }}
                        </div>
                        <div class="text-secondary pl-2">
                            {{ line.price }}
                        </div>
                    </li>
                </ul>
                <div class="text-right mt-3">
                    <a :href="order.order_status_url" class="btn btn-sm btn-primary" target="_blank">Go to Order <i class="fas fa-angle-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
// import apiRequest from '../functions/apiRequest';
export default {
    name: 'Shopify',
    props: {
        orders: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            polling: null,
            dataOrders: this.orders
        }
    }
    // created() {
    //     this.pollData();
    // },

    // beforeDestroy() {
    //     clearInterval(this.polling);
    // },

    // methods: {
    //     pollData() {
    //         this.polling = setInterval(() => {
    //             apiRequest('/api/webhooks/shopify/get').then(response => this.dataOrders = response);
    //         }, 10000);
    //     }
    // }
};
</script>
