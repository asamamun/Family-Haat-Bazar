</div><!-- main content end -->
</div><!-- end of container-->
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <h5><i class="fas fa-shopping-bag me-2"></i>ShopEase</h5>
                    <p class="text-muted">Your trusted online shopping partner delivering quality products to your doorstep.</p>
                    <div class="mt-3">
                        <img src="https://via.placeholder.com/120x40?text=Payment+Methods" alt="Payment Methods" class="img-fluid">
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>About Us</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Contact</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Privacy Policy</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Terms & Conditions</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Return Policy</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6>Categories</h6>
                    <ul class="list-unstyled">
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Electronics</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Fashion</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Home & Kitchen</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Beauty</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Groceries</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6>Contact Info</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Street, Dhaka, Bangladesh</li>
                        <li><i class="fas fa-phone me-2"></i> +880 1700-000000</li>
                        <li><i class="fas fa-envelope me-2"></i> info@shopease.com</li>
                    </ul>
                    <div class="mt-3 social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                    <div class="mt-3">
                        <h6>Download Our App</h6>
                        <div class="d-flex gap-2">
                            <a href="#"><img src="https://via.placeholder.com/120x40?text=App+Store" alt="App Store" class="img-fluid"></a>
                            <a href="#"><img src="https://via.placeholder.com/120x40?text=Play+Store" alt="Play Store" class="img-fluid"></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row copyright">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; <script>document.write(new Date().getFullYear())</script> ShopEase. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0">Designed with <i class="fas fa-heart text-danger"></i> by ShopEase Team</p>
                </div>
            </div>
        </div>
    </footer>


<script src="<?= settings()['homepage'] ?>assets/owl.carousel.min.js"></script>

<script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
(() => {
  'use strict'

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  const forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }

      form.classList.add('was-validated')
    }, false)
  })
})()
</script>
<script>
        // Toggle category sidebar
        document.getElementById('categoryToggle').addEventListener('click', function() {
            document.getElementById('categorySidebar').classList.add('show');
            document.getElementById('sidebarOverlay').classList.add('show');
        });

        // Close category sidebar
        document.getElementById('closeSidebar').addEventListener('click', function() {
            document.getElementById('categorySidebar').classList.remove('show');
            document.getElementById('sidebarOverlay').classList.remove('show');
        });

        // Close sidebar when clicking overlay
        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.getElementById('categorySidebar').classList.remove('show');
            this.classList.remove('show');
        });

        // Toggle subcategories
        document.querySelectorAll('.category-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const item = this.parentElement;
                const sublist = item.querySelector('.subcategory-list');
                sublist.classList.toggle('show');
                
                // Rotate chevron icon
                const chevron = this.querySelector('.fa-chevron-right');
                if (chevron) {
                    chevron.classList.toggle('rotate-90');
                }
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            // show the cart items in #cartContent
            let allitems = cart.getSummary();
            showCartItemsOffCanvas(allitems.items);
        });
        function showCartItemsOffCanvas(items) {
            let cartItems = '';
            items.forEach(item => {
                cartItems += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.quantity}</td>
                        <td>${item.price}</td>
                        <td>${item.quantity * item.price}</td>
                        <td><a href="#" class="remove-item" data-id="${item.id}"><i class="fas fa-times"></i></a></td>
                    </tr>
                `;
            });
            $('#cartContent table tbody').html(cartItems);
        }
    </script>
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-9RHP7E8KTP"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-9RHP7E8KTP');
</script>
