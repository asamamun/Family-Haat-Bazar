class Cart {
  constructor() {
    this.cartKey = 'ecommerce_cart';
    this.loadCart();
  }

  // Load cart from localStorage
  loadCart() {
    const cartData = localStorage.getItem(this.cartKey);
    this.items = cartData ? JSON.parse(cartData) : [];
  }

  // Save cart to localStorage
  saveCart() {
    localStorage.setItem(this.cartKey, JSON.stringify(this.items));
  }

  // Add item to cart
  addItem(item) {
    // console.log(item);
    if (!item.id || !item.name || !item.price || !item.quantity) {
      throw new Error('Item must have id, name, price, and quantity');
    }

    const existingItem = this.items.find(cartItem => cartItem.id === item.id);
    if (existingItem) {
      existingItem.quantity += item.quantity;
    } else {
      this.items.push({
        id: item.id,
        name: item.name,
        price: parseFloat(item.price),
        quantity: parseInt(item.quantity),
        image: item.image || '',
        attributes: item.attributes || {}
      });
    }
    this.saveCart();
    return this.items;
  }

  // Edit item quantity
  editItem(itemId, quantity) {
    const item = this.items.find(cartItem => cartItem.id === itemId);
    if (!item) {
      throw new Error('Item not found in cart');
    }
    if (quantity <= 0) {
      this.removeItem(itemId);
    } else {
      item.quantity = parseInt(quantity);
      this.saveCart();
    }
    return this.items;
  }

  // Remove item from cart
  removeItem(itemId) {
    this.items = this.items.filter(item => item.id !== itemId);
    this.saveCart();
    return this.items;
  }

  // Get total number of items
  getTotalItems() {
    return this.items.reduce((total, item) => total + item.quantity, 0);
  }

  // Get total price
  getTotalPrice() {
    return this.items.reduce((total, item) => total + (item.price * item.quantity), 0).toFixed(2);
  }

  // Get cart items
  getItems() {
    return [...this.items];
  }

  // Clear cart
  clearCart() {
    this.items = [];
    this.saveCart();
    return this.items;
  }

  // Get item by ID
  getItemById(itemId) {
    return this.items.find(item => item.id === itemId) || null;
  }

  // Check if cart is empty
  isEmpty() {
    return this.items.length === 0;
  }

  // Apply discount to total
  applyDiscount(discountPercentage) {
    if (discountPercentage < 0 || discountPercentage > 100) {
      throw new Error('Invalid discount percentage');
    }
    const total = this.getTotalPrice();
    const discount = (total * (discountPercentage / 100)).toFixed(2);
    return (total - discount).toFixed(2);
  }

  // Merge another cart (useful for user login)
  mergeCart(newItems) {
    newItems.forEach(newItem => {
      const existingItem = this.items.find(item => item.id === newItem.id);
      if (existingItem) {
        existingItem.quantity += newItem.quantity;
      } else {
        this.items.push(newItem);
      }
    });
    this.saveCart();
    return this.items;
  }

  // Get cart summary
  getSummary() {
    return {
      items: this.getItems(),
      totalItems: this.getTotalItems(),
      totalPrice: this.getTotalPrice(),
      isEmpty: this.isEmpty()
    };
  }
}

// Global functions for cart display
console.log("Defining populateItems function...");
function populateItems(items, tableId) {
    $(tableId).html("");
    if (items.length === 0) {
        $("#cartEmptyMessage").removeClass("d-none");
        $("#cartTableMain").addClass("d-none");
        return;
    } else {
        $("#cartEmptyMessage").addClass("d-none");
        $("#cartTableMain").removeClass("d-none");
    }
    items.forEach(item => {
        $(tableId).append(`
            <tr>
                <td class="align-middle">${item.name}</td>
                <td class="align-middle">
                    <input type="number" class="form-control form-control-sm qty-input" data-id="${item.id}" value="${item.quantity}" min="1" style="width: 80px;">
                </td>
                <td class="align-middle">৳${parseFloat(item.price).toFixed(2)}</td>
                <td class="align-middle">৳${(item.quantity * item.price).toFixed(2)}</td>
                <td class="align-middle">
                    <button class="btn btn-danger btn-sm remove-item" data-id="${item.id}">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                </td>
            </tr>
        `);
    });
}

const cart = new Cart(); // Initialize cart globally

console.log("Defining window.updateCartDisplay function...");
window.updateCartDisplay = function() {
    let allitems = cart.getSummary();
    $("#cartCountButton").text(cart.getTotalItems());
    $("#grandTotal").text(parseFloat(cart.getTotalPrice()).toFixed(2));
    populateItems(allitems.items, "#cartTable");
    populateItems(allitems.items, "#cartContent table tbody");

    // Update offcanvas cart content
    let cartItemsOffCanvas = '';
    allitems.items.forEach(item => {
        cartItemsOffCanvas += `
            <tr>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>${item.price}</td>
                <td>${(item.quantity * item.price).toFixed(2)}</td>
                <td><a href="#" class="remove-item" data-id="${item.id}"><i class="fas fa-times"></i></a></td>
            </tr>
        `;
    });
    $('#cartContent table tbody').html(cartItemsOffCanvas);
    $('#grandTotalCanvas').text(parseFloat(cart.getTotalPrice()).toFixed(2));
};

$(document).ready(function() {
    console.log("cart.js: Document ready. Calling updateCartDisplay()...");
    // Initial cart load
    updateCartDisplay();

    // Event delegation for dynamically added elements
    $(document).on("click", ".remove-item", function() {
        let id = $(this).data('id');
        cart.removeItem(id);
        updateCartDisplay();
        Swal.fire({
            icon: 'success',
            title: 'Item Removed',
            text: 'The item has been removed from your cart.',
            timer: 1500,
            showConfirmButton: false
        });
    });

    $(document).on("change", ".qty-input", function() {
        let id = $(this).data('id');
        let quantity = parseInt($(this).val());
        if (quantity < 1) {
            $(this).val(1);
            quantity = 1;
        }
        cart.editItem(id, quantity);
        updateCartDisplay();
    });

    $(document).on("click", ".btn-add-cart", function() {
        console.log("Add to cart button clicked.");
        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name');
        const productPrice = $(this).data('product-price');
        const productImage = $(this).data('product-image');
        
        cart.addItem({
            id: productId,
            name: productName,
            price: productPrice,
            quantity: 1,
            image: productImage
        });
        
        updateCartDisplay();

        Swal.fire({
            position: "top-end",
            icon: "success",
            title: "Item added to cart",
            showConfirmButton: false,
            timer: 1500
        });
    });

    // Update offcanvas cart when shown
    $('#offcanvasCart').on('show.bs.offcanvas', function () {
        updateCartDisplay();
    });
});
