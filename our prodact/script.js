document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
});

async function loadProducts() {
    try {
        const response = await fetch('get_products.php');
        const products = await response.json();

        // Group products by category
        const categories = {
            'women': document.getElementById('women-products'),
            'men': document.getElementById('men-products'),
            'kids': document.getElementById('kids-products'),
            'accessories': document.getElementById('accessories-products')
        };

        products.forEach(product => {
            if (categories[product.category]) {
                const productCard = createProductCard(product);
                categories[product.category].appendChild(productCard);
            }
        });
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'product-card';

    const discount = product.original_price ?
        Math.round(((product.original_price - product.price) / product.original_price) * 100) : 0;

    card.innerHTML = `
        ${discount > 0 ? `<div class="discount-badge">-${discount}%</div>` : ''}
        <img src="${product.image_path}" alt="${product.name}" class="product-image">
        <div class="product-info">
            <h3 class="product-title">${product.name}</h3>
            <p class="description">${product.description}</p>
            <div class="price">
                $${product.price.toFixed(2)}
                ${product.original_price ? `<span class="original-price">$${product.original_price.toFixed(2)}</span>` : ''}
            </div>
            <button class="quicklook-btn" onclick="showProductDetails(${product.id})">Quick Look</button>
        </div>
    `;
    
    return card;
}

function showProductDetails(productId) {
    // Implementation for showing product details in modal
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    
    fetch(`get_product_details.php?id=${productId}`)
        .then(response => response.json())
        .then(product => {
            document.querySelector('.product-detail-image').src = product.image_path;
            document.querySelector('.product-detail-name').textContent = product.name;
            document.querySelector('.product-detail-description').textContent = product.description;
            document.querySelector('.product-detail-price').textContent = `$${product.price.toFixed(2)}`;
            
            // Populate color and size options if available
            // ...

            modal.show();
        })
        .catch(error => console.error('Error loading product details:', error));
}
