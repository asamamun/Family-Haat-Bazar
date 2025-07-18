</div><!-- main content end -->
</div><!-- end of container-->
    <!-- Social Sharing Section -->
    <section class="social-sharing-section py-4">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h6 class="mb-3">
                        <i class="fas fa-share-alt me-2"></i>Share This Page
                    </h6>
                    <div class="social-share-buttons">
                        <button class="btn btn-facebook" onclick="shareOnFacebook()">
                            <i class="fab fa-facebook-f me-2"></i>Facebook
                        </button>
                        <button class="btn btn-twitter" onclick="shareOnTwitter()">
                            <i class="fab fa-twitter me-2"></i>Twitter
                        </button>
                        <button class="btn btn-whatsapp" onclick="shareOnWhatsApp()">
                            <i class="fab fa-whatsapp me-2"></i>WhatsApp
                        </button>
                        <button class="btn btn-linkedin" onclick="shareOnLinkedIn()">
                            <i class="fab fa-linkedin-in me-2"></i>LinkedIn
                        </button>
                        <button class="btn btn-telegram" onclick="shareOnTelegram()">
                            <i class="fab fa-telegram-plane me-2"></i>Telegram
                        </button>
                        <button class="btn btn-copy" onclick="copyPageLink()">
                            <i class="fas fa-link me-2"></i>Copy Link
                        </button>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">Help us grow by sharing with your friends and family!</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

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

<!-- Social Sharing JavaScript -->
<script>
// Get current page information
function getCurrentPageInfo() {
    return {
        url: window.location.href,
        title: document.title,
        description: document.querySelector('meta[name="description"]')?.content || 
                    document.querySelector('meta[property="og:description"]')?.content || 
                    'Check out this amazing product from <?= settings()['companyname'] ?>!',
        image: document.querySelector('meta[property="og:image"]')?.content || 
               '<?= settings()['homepage'] . ltrim(settings()['logo'], '/') ?>'
    };
}

// Facebook Share
function shareOnFacebook() {
    const pageInfo = getCurrentPageInfo();
    const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(pageInfo.url)}`;
    openShareWindow(shareUrl, 'Facebook Share');
}

// Twitter Share
function shareOnTwitter() {
    const pageInfo = getCurrentPageInfo();
    const text = `${pageInfo.title} - ${pageInfo.description}`;
    const shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(pageInfo.url)}&text=${encodeURIComponent(text)}&hashtags=HaatBazar,Shopping,Bangladesh`;
    openShareWindow(shareUrl, 'Twitter Share');
}

// WhatsApp Share
function shareOnWhatsApp() {
    const pageInfo = getCurrentPageInfo();
    const text = `${pageInfo.title}\n${pageInfo.description}\n${pageInfo.url}`;
    const shareUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
    openShareWindow(shareUrl, 'WhatsApp Share');
}

// LinkedIn Share
function shareOnLinkedIn() {
    const pageInfo = getCurrentPageInfo();
    const shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(pageInfo.url)}`;
    openShareWindow(shareUrl, 'LinkedIn Share');
}

// Telegram Share
function shareOnTelegram() {
    const pageInfo = getCurrentPageInfo();
    const text = `${pageInfo.title}\n${pageInfo.description}`;
    const shareUrl = `https://t.me/share/url?url=${encodeURIComponent(pageInfo.url)}&text=${encodeURIComponent(text)}`;
    openShareWindow(shareUrl, 'Telegram Share');
}

// Copy Link
function copyPageLink() {
    const pageInfo = getCurrentPageInfo();
    navigator.clipboard.writeText(pageInfo.url).then(() => {
        // Show success message
        showCopySuccess();
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = pageInfo.url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showCopySuccess();
    });
}

// Open share window
function openShareWindow(url, title) {
    const width = 600;
    const height = 400;
    const left = (window.innerWidth - width) / 2;
    const top = (window.innerHeight - height) / 2;
    
    window.open(
        url,
        title,
        `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`
    );
}

// Show copy success message
function showCopySuccess() {
    // Create and show a temporary success message
    const message = document.createElement('div');
    message.innerHTML = '<i class="fas fa-check me-2"></i>Link copied to clipboard!';
    message.className = 'alert alert-success position-fixed';
    message.style.cssText = 'top: 20px; right: 20px; z-index: 9999; padding: 10px 20px; border-radius: 5px;';
    
    document.body.appendChild(message);
    
    setTimeout(() => {
        message.remove();
    }, 3000);
}

// Native Web Share API (for mobile devices)
function shareNative() {
    const pageInfo = getCurrentPageInfo();
    
    if (navigator.share) {
        navigator.share({
            title: pageInfo.title,
            text: pageInfo.description,
            url: pageInfo.url
        }).catch(console.error);
    }
}

// Check if device supports native sharing and add button
document.addEventListener('DOMContentLoaded', function() {
    if (navigator.share) {
        const shareButtons = document.querySelector('.social-share-buttons');
        const nativeShareBtn = document.createElement('button');
        nativeShareBtn.className = 'btn btn-native-share';
        nativeShareBtn.innerHTML = '<i class="fas fa-share me-2"></i>Share';
        nativeShareBtn.onclick = shareNative;
        shareButtons.appendChild(nativeShareBtn);
    }
});
</script>

<!-- Social Sharing CSS -->
<style>
.social-sharing-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    margin-top: 2rem;
}

.social-share-buttons {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    margin-bottom: 1rem;
}

.social-share-buttons .btn {
    border-radius: 25px;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    min-width: 120px;
    justify-content: center;
}

.btn-facebook {
    background: linear-gradient(45deg, #1877f2, #42a5f5);
}

.btn-facebook:hover {
    background: linear-gradient(45deg, #166fe5, #1976d2);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(24, 119, 242, 0.4);
}

.btn-twitter {
    background: linear-gradient(45deg, #1da1f2, #42a5f5);
}

.btn-twitter:hover {
    background: linear-gradient(45deg, #1a91da, #1976d2);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(29, 161, 242, 0.4);
}

.btn-whatsapp {
    background: linear-gradient(45deg, #25d366, #4caf50);
}

.btn-whatsapp:hover {
    background: linear-gradient(45deg, #20ba5a, #388e3c);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
}

.btn-linkedin {
    background: linear-gradient(45deg, #0077b5, #42a5f5);
}

.btn-linkedin:hover {
    background: linear-gradient(45deg, #005885, #1976d2);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 119, 181, 0.4);
}

.btn-telegram {
    background: linear-gradient(45deg, #0088cc, #42a5f5);
}

.btn-telegram:hover {
    background: linear-gradient(45deg, #006699, #1976d2);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 136, 204, 0.4);
}

.btn-copy {
    background: linear-gradient(45deg, #6c757d, #9e9e9e);
}

.btn-copy:hover {
    background: linear-gradient(45deg, #5a6268, #757575);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
}

.btn-native-share {
    background: linear-gradient(45deg, #ff6b6b, #ff8a80);
}

.btn-native-share:hover {
    background: linear-gradient(45deg, #ff5252, #ff6b6b);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .social-share-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .social-share-buttons .btn {
        width: 200px;
        margin-bottom: 8px;
    }
}

/* Animation for buttons */
.social-share-buttons .btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Hover effect for icons */
.social-share-buttons .btn i {
    transition: transform 0.3s ease;
}

.social-share-buttons .btn:hover i {
    transform: scale(1.1);
}
</style>
