<template>
    <div class="product-card p-2 mb-1">
        <div class="product-body">
            <div class="product-row relative">
                <div class="product-image">
                    <img :src="product.icon" :alt="product.name">
                </div>
                <div class="product-description">
                    <div>
                        <p class="product-name">{{product.name}}</p>
                        <p>{{product.description}}</p>
                    </div>
                </div>
                <div class="absolute">
                    <button v-if="!product.installed"
                            class="btn btn-info"
                            data-control="popup"
                            data-handler="onInstallPlugin"
                            :data-request-data="`package: '${product.package}'`"
                    >Install</button>
                    <p v-if="product.installed" class="text-muted">This {{type}} is installed.</p>
                </div>
            </div>
        </div>
        <div class="product-footer">
            <div class="product-footer-item">
                <div title="Stars given" class="stars">
                    <span class="product-badge"><i class="icon-star"></i></span>
                    {{product.favers}}
                </div>
                <div title="Downloads" class="downloads">
                    <span class="product-badge"><i class="icon-download"></i></span>
                    {{product.downloads}}
                </div>
            </div>
            <div class="product-footer-item">
                <a :href="product.repository" target="_blank" rel="noopener" title="GitHub" class="github">
                    <span class="product-badge"><i class="icon-github"></i></span>
                </a>
                <a :href="product.url" target="_blank" rel="noopener" title="Packagist" class="packagist">
                    <span class="product-badge"><i class="icon-download"></i></span>
                </a>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ['product', 'type'],
};
</script>
<style>
.product-card {
    flex: 1 1 500px;
    box-sizing: border-box;
    margin: 1rem .25em;
}

@media screen and (min-width: 40em) {
    .product-card {
        max-width: calc(50% -  1em);
    }
}

@media screen and (min-width: 60em) {
    .product-card {
        max-width: calc(33.3333% - 1em);
    }
}
.product-name {
    font-size: 18px;
    color: #1991d1;
    text-wrap: wrap;
}
.product-body {
    border: 2px solid #cdcdcd;
    border-bottom: 0;
    border-top-right-radius: 4px;
    border-top-left-radius: 4px;
    padding: 10px 15px;
    width: auto;
    align-items: stretch;
    min-height: 82%;
    text-wrap: wrap;
}
.product-description {
    margin-left: 10px;
}
.product-body .relative {
    position: relative;
    display: block;
    text-wrap: wrap;
}
.product-body .absolute {
    position: absolute;
    right: 5px;
    top: 5px;
}
.product-footer {
    background: #ececec;
    border: 2px solid #cdcdcd;
    border-top: 0;
    border-bottom-right-radius: 4px;
    border-bottom-left-radius: 4px;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    gap: 15px;
}
.product-image {
    width: 35%;
    border-radius: 6px;
    margin: 10px;
    overflow: hidden;
}
.product-image img {
    width: -webkit-fill-available;
}
.mb-1 {
    margin-bottom: 1rem;
}
.product-row {
    display: flex;
    align-self: stretch;
}
.product-footer-item {
    display: flex;
}
.product-footer .product-badge {
    color: white;
    padding: 6px;
    border-radius: 6px;
}
.product-footer .stars .product-badge {
    background: #f0ad4e;
}
.product-footer .downloads .product-badge {
    background: #183638;
}
.product-footer .github .product-badge {
    background: #010409;
}
.product-footer .packagist .product-badge {
    background: #f28d1a;
}
.product-footer .stars, .product-footer .github {
    margin-right: 7px;
}
</style>


