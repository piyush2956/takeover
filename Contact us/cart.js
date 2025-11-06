document.addEventListener('scroll', () => {
    document.querySelectorAll('.card').forEach(card => {
        if (card.getBoundingClientRect().top < window.innerHeight - 50) {
            card.classList.add('visible');
        }
    });
});
window.addEventListener('DOMContentLoaded', (event) => {
    // Add animation styles dynamically
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        .category {
            animation: fadeIn 0.8s ease-out;
        }
        .category-btn.active {
            background: linear-gradient(45deg, #FF6B6B, #4ECDC4);
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(0,123,255,0.5);
        }
        .product-card {
            animation: slideIn 0.5s ease-out;
        }
    `;
    document.head.appendChild(style);

    // Initially hide all categories
    document.querySelectorAll('.category').forEach(category => {
        category.style.display = 'none';
    });

    // Show first category with animation
    const firstCategory = document.querySelector('.category');
    firstCategory.style.display = 'block';
    document.querySelector('.category-btn').classList.add('active');

    // Enhanced click handlers for category buttons
    document.querySelectorAll('.category-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active class from all buttons
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Add active class to clicked button
            this.classList.add('active');

            // Hide all categories with fade out
            document.querySelectorAll('.category').forEach(category => {
                category.style.opacity = '0';
                setTimeout(() => {
                    category.style.display = 'none';
                }, 300);
            });

            // Show selected category with animation
            const targetId = this.getAttribute('href').substring(1);
            const categories = document.querySelectorAll('.category');
            let targetCategory;

            if (targetId === 'women') targetCategory = categories[0];
            else if (targetId === 'men') targetCategory = categories[1];
            else if (targetId === 'kids') targetCategory = categories[2];
            else if (targetId === 'accessories') targetCategory = categories[3];

            if (targetCategory) {
                setTimeout(() => {
                    targetCategory.style.display = 'block';
                    targetCategory.style.opacity = '1';
                    targetCategory.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 300);
            }
        });
    });

    // Add hover effects to product cards
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('mouseenter', (e) => {
            card.style.transform = 'scale(1.05) translateY(-10px)';
            card.style.boxShadow = '0 15px 30px rgba(0,0,0,0.3)';
        });
        card.addEventListener('mouseleave', (e) => {
            card.style.transform = 'scale(1) translateY(0)';
            card.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
        });
    });

    // Load initial products for each category
    ['women', 'men', 'kids', 'accessories'].forEach(category => {
        loadProducts(category);
    });
});
window.addEventListener('DOMContentLoaded', (event) => {
    // Move category nav to top
    const categoryNav = document.querySelector('.category-nav');
    const categoriesContainer = document.querySelector('.categories-container');
    if (categoryNav && categoriesContainer) {
        categoriesContainer.parentNode.insertBefore(categoryNav, categoriesContainer);
    }

    // Replace all "Add to Cart" buttons with "Quick Look"
    document.querySelectorAll('.btn-primary').forEach(button => {
        const quickLookBtn = document.createElement('button');
        quickLookBtn.className = 'quicklook-btn';
        quickLookBtn.textContent = 'Quick Look';
        button.parentNode.replaceChild(quickLookBtn, button);
    });

    // Smooth scrolling
    document.querySelectorAll('.category-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    });
});
// Update cart count based on localStorage
function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    let totalItems = 0;
    cart.forEach(item => totalItems += item.quantity);
    document.querySelector('.badge').textContent = totalItems;
}
// Initial update
updateCartCount();
// Update whenever localStorage changes
window.addEventListener('storage', updateCartCount);
window.addEventListener('DOMContentLoaded', (event) => {
    // Check for hash in URL
    const hash = window.location.hash;
    if (hash) {
        // Remove the # symbol
        const targetId = hash.substring(1);

        // Find the corresponding category button and trigger its click
        const categoryBtn = document.querySelector(`.category-btn[href="#${targetId}"]`);
        if (categoryBtn) {
            // Small delay to ensure DOM is ready
            setTimeout(() => {
                categoryBtn.click();
            }, 100);
        }
    }
});

function loadProducts(category) {
    const productsGrid = document.querySelector(`#${category}-products`);
    if (!productsGrid) return;

    productsGrid.innerHTML = '<div class="loading">Loading...</div>';

    fetch(`get_products.php?category=${category}`)
        .then(response => response.text())
        .then(html => {
            productsGrid.innerHTML = html;
            initializeProductCards();
        })
        .catch(error => {
            console.error('Error:', error);
            productsGrid.innerHTML = 'Error loading products';
        });
}

function initializeProductCards() {
    document.querySelectorAll('.product-card').forEach(card => {
        // Existing hover effects
        card.addEventListener('mouseenter', (e) => {
            card.style.transform = 'scale(1.05) translateY(-10px)';
            card.style.boxShadow = '0 15px 30px rgba(0,0,0,0.3)';
        });
        card.addEventListener('mouseleave', (e) => {
            card.style.transform = 'scale(1) translateY(0)';
            card.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
        });

        // Add image indicator functionality
        const indicators = card.querySelectorAll('.indicator');
        const mainImage = card.querySelector('.main-image');

        indicators.forEach(indicator => {
            indicator.addEventListener('click', () => {
                // Update active indicator
                card.querySelectorAll('.indicator').forEach(ind => ind.classList.remove('active'));
                indicator.classList.add('active');

                // Update main image
                mainImage.src = indicator.dataset.image;
            });

            // Optional: Auto-switch images on hover
            indicator.addEventListener('mouseenter', () => {
                mainImage.src = indicator.dataset.image;
            });
        });
    });
}

function quickLook(productId, images) {
    const modal = document.getElementById('quickLookModal');
    const modalInstance = new bootstrap.Modal(modal);

    fetch(`get_product_details.php?id=${productId}`)
        .then(response => response.json())
        .then(product => {
                // Update image carousel
                const imageContainer = modal.querySelector('.image-container');
                imageContainer.innerHTML = `
                <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        ${images.map((img, index) => `
                            <div class="carousel-item ${index === 0 ? 'active' : ''}">
                                <img src="${img}" alt="Product image ${index + 1}">
                            </div>
                        `).join('')}
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
                <div class="thumbnail-nav">
                    ${images.map((img, index) => `
                        <img src="${img}" 
                             class="thumb-img ${index === 0 ? 'active' : ''}" 
                             data-bs-slide-to="${index}" 
                             data-bs-target="#productCarousel"
                             alt="Thumbnail ${index + 1}">
                    `).join('')}
                </div>
            `;

            // Update product details
            modal.querySelector('.product-title').textContent = product.name;
            modal.querySelector('.current-price').textContent = `${product.price} MAD`;
            modal.querySelector('.description').textContent = product.description;
            
            // Update size options
            const sizeOptions = modal.querySelector('#modal-size-options');
            sizeOptions.innerHTML = product.sizes.map(size => 
                `<span class="size-option">${size}</span>`
            ).join('');

            // Update color options
            const colorOptions = modal.querySelector('#modal-color-options');
            colorOptions.innerHTML = product.colors.map(color => 
                `<span class="color-option ${color}" title="${color}"></span>`
            ).join('');

            // Update stock info
            const quantityInput = modal.querySelector('.quantity-input');
            quantityInput.max = product.stock;
            modal.querySelector('.stock-info').textContent = `${product.stock} items available`;

            // Show modal
            modalInstance.show();

            // Initialize event listeners
            initializeModalListeners(modal, product);
        });
}

function initializeModalListeners(modal, product) {
    // Size selection
    modal.querySelectorAll('.size-option').forEach(option => {
        option.addEventListener('click', () => {
            modal.querySelectorAll('.size-option').forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');
        });
    });

    // Color selection
    modal.querySelectorAll('.color-option').forEach(option => {
        option.addEventListener('click', () => {
            modal.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');
        });
    });

    // Quantity validation
    const quantityInput = modal.querySelector('.quantity-input');
    quantityInput.addEventListener('input', () => {
        let value = parseInt(quantityInput.value);
        if (isNaN(value) || value < 1) value = 1;
        if (value > product.stock) value = product.stock;
        quantityInput.value = value;
    });

    // Add to cart button
    modal.querySelector('.add-to-cart-btn').addEventListener('click', () => {
        const size = modal.querySelector('.size-option.selected')?.textContent;
        const color = modal.querySelector('.color-option.selected')?.classList[1];
        
        if (!size || !color) {
            alert('Please select both size and color');
            return;
        }

        addToCart({
            id: product.id,
            name: product.name,
            price: product.price,
            size: size,
            color: color,
            quantity: parseInt(quantityInput.value),
            image: modal.querySelector('.carousel-item.active img').src
        });
    });
}