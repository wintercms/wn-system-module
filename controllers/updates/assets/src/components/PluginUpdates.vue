<template>
    <div>
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="btn-group" role="group" aria-label="...">
                    <button type="button"
                            :class="`btn btn-${active === 'popular' ? 'primary' : 'default'}`"
                            @click="activePlugins = 'popular'; filter = null;"
                    >Popular</button>
                    <button type="button"
                            :class="`btn btn-${active === 'featured' ? 'primary' : 'default'}`"
                            @click="activePlugins = 'featured'; filter = null;"
                    >Featured</button>
                    <button type="button"
                            :class="`btn btn-${active === 'all' ? 'primary' : 'default'}`"
                            @click="activePlugins = 'all'; filter = null;"
                    >All</button>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="product-search">
                    <input
                        ref="search"
                        name="code"
                        id="pluginSearchInput"
                        class="product-search-input search-input-lg typeahead"
                        :placeholder="searchString"
                        data-search-type="plugins"
                        @keydown="filter = this.$refs.search.value; activePlugins = 'all';"
                    />
                    <i class="icon icon-search"></i>
                    <i class="icon loading" style="display: none"></i>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <button
                    type="button"
                    data-control="popup"
                    data-handler="onLoadPluginUploader"
                    tabindex="-1"
                    class="btn btn-success wn-icon-file-arrow-up"
                >
                    {{uploadString}}
                </button>
            </div>
        </div>
        <div class="products row m-t-sm">
            <Product v-for="plugin in activePlugins" :product="plugin" type="plugin"></Product>
        </div>
    </div>
</template>
<script>
import Product from "./Product.vue";

export default {
    components: {Product},
    props: ['searchString', 'uploadString'],
    data: () => ({
        active: 'popular',
        plugins: {},
        filter: null
    }),
    computed: {
        activePlugins: {
            get() {
                if (this.filter) {
                    return this.plugins.all.filter((plugin) => {
                        return plugin.name.includes(this.filter)
                            || plugin.description.includes(this.filter)
                            || plugin.package.includes(this.filter);
                    });
                }
                return this.plugins[this.active];
            },
            set(value) {
                this.active = value;
            }
        }
    },
    mounted() {
        this.$request('onGetMarketplacePlugins', {
            success: (response) => {
                this.plugins = response.result;
            }
        });
    }
};
</script>
<style>
.typeahead {
    height: 36px;
    font-size: 18px;
}
.products {
    display: flex;
    flex-wrap: wrap;
}
</style>
