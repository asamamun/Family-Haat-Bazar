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