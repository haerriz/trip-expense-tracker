// Blog functionality for Haerriz Trip Finance
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Materialize components
    M.AutoInit();

    // Newsletter signup
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('newsletter-email').value;

            if (email) {
                // Here you would send the email to your backend
                M.toast({html: 'Thanks for subscribing! Check your email for travel finance tips.'});
                document.getElementById('newsletter-email').value = '';
            } else {
                M.toast({html: 'Please enter a valid email address.'});
            }
        });
    }

    // Blog post navigation
    const blogCards = document.querySelectorAll('.card .btn, .card .btn-small');
    blogCards.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                const postId = href.substring(1);
                showBlogPost(postId);
            }
        });
    });

    // Blog post reading functionality
    function showBlogPost(postId) {
        const blogPosts = document.getElementById('blog-posts');
        const post = document.getElementById(postId);

        if (post) {
            // Hide main blog grid
            document.querySelector('.row').style.display = 'none';

            // Show blog posts container
            blogPosts.style.display = 'block';

            // Hide all posts except the selected one
            const allPosts = blogPosts.querySelectorAll('.blog-post');
            allPosts.forEach(p => p.style.display = 'none');

            // Show selected post
            post.style.display = 'block';

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Update URL without page reload
            history.pushState(null, null, `#${postId}`);
        }
    }

    // Handle browser back button
    window.addEventListener('popstate', function() {
        const hash = window.location.hash;
        if (hash) {
            const postId = hash.substring(1);
            showBlogPost(postId);
        } else {
            // Show main blog grid
            document.querySelector('.row').style.display = 'block';
            document.getElementById('blog-posts').style.display = 'none';
        }
    });

    // Check for hash on page load
    if (window.location.hash) {
        const postId = window.location.hash.substring(1);
        setTimeout(() => showBlogPost(postId), 100);
    }

    // Social sharing functionality
    function initSocialSharing() {
        const shareButtons = document.querySelectorAll('.share-btn');
        shareButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const platform = this.dataset.platform;
                const url = encodeURIComponent(window.location.href);
                const title = encodeURIComponent(document.title);

                let shareUrl = '';

                switch(platform) {
                    case 'twitter':
                        shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}&via=haerriz`;
                        break;
                    case 'facebook':
                        shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                        break;
                    case 'linkedin':
                        shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
                        break;
                }

                if (shareUrl) {
                    window.open(shareUrl, '_blank', 'width=600,height=400');
                }
            });
        });
    }

    // Reading progress indicator
    function initReadingProgress() {
        const progressBar = document.createElement('div');
        progressBar.className = 'reading-progress';
        progressBar.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, #2196F3, #1976D2);
            z-index: 1000;
            transition: width 0.25s ease;
        `;
        document.body.appendChild(progressBar);

        function updateProgress() {
            const scrollTop = window.pageYOffset;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercent = (scrollTop / docHeight) * 100;
            progressBar.style.width = scrollPercent + '%';
        }

        window.addEventListener('scroll', updateProgress);
        updateProgress();
    }

    // Initialize features when blog posts are shown
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                const blogPosts = document.getElementById('blog-posts');
                if (blogPosts.style.display !== 'none') {
                    initSocialSharing();
                    initReadingProgress();
                }
            }
        });
    });

    const blogPostsContainer = document.getElementById('blog-posts');
    if (blogPostsContainer) {
        observer.observe(blogPostsContainer, { attributes: true });
    }

    // Search functionality
    const searchInput = document.getElementById('blog-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const cards = document.querySelectorAll('.card');

            cards.forEach(card => {
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                const content = card.querySelector('.card-content p').textContent.toLowerCase();

                if (title.includes(query) || content.includes(query)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Lazy loading for images
    function initLazyLoading() {
        const images = document.querySelectorAll('img[data-src]');

        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    initLazyLoading();

    // Analytics tracking (placeholder)
    function trackEvent(event, data) {
        // Here you would send data to your analytics service
        console.log('Analytics event:', event, data);
    }

    // Track blog post views
    document.addEventListener('click', function(e) {
        if (e.target.matches('.card .btn, .card .btn-small')) {
            const card = e.target.closest('.card');
            const title = card.querySelector('.card-title').textContent;
            trackEvent('blog_post_click', { title: title });
        }
    });

    // Track newsletter signups
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function() {
            trackEvent('newsletter_signup', { source: 'blog_page' });
        });
    }
});