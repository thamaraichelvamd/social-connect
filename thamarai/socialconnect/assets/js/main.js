/**
 * SocialConnect - Main JavaScript
 * Version: 1.0
 */

$(document).ready(function() {
    // Initialize components
    initializeHeader();
    initializeSidebar();
    initializeAlerts();
    initializeForms();
    initializeModals();
    initializePosts();
    initializeTooltips();
});

// ============================================
// Header Functions
// ============================================
function initializeHeader() {
    // Mobile menu toggle
    $('.header__menu-toggle').on('click', function() {
        $('.sidebar').toggleClass('open');
        $('body').toggleClass('sidebar-open');
    });
    
    // Search functionality
    const $searchInput = $('.header__search input');
    let searchTimeout;
    
    $searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        } else {
            hideSearchResults();
        }
    });
    
    // User dropdown
    $('.header__user').on('click', function(e) {
        e.stopPropagation();
        $(this).find('.header__dropdown').toggleClass('show');
    });
    
    // Close dropdowns on outside click
    $(document).on('click', function() {
        $('.header__dropdown').removeClass('show');
    });
    
    // Notifications dropdown
    $('.header__link[data-toggle="notifications"]').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        loadNotifications();
        $(this).find('.header__notifications').toggleClass('show');
    });
}

function performSearch(query) {
    $.ajax({
        url: 'api/search.php',
        method: 'GET',
        data: { q: query },
        success: function(response) {
            displaySearchResults(response.data);
        },
        error: function() {
            console.error('Search failed');
        }
    });
}

function displaySearchResults(results) {
    // Implementation for search results dropdown
}

function hideSearchResults() {
    $('.search-results').removeClass('show');
}

function loadNotifications() {
    $.ajax({
        url: 'api/notifications.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                renderNotifications(response.data);
            }
        }
    });
}

function renderNotifications(notifications) {
    const $container = $('.header__notifications-list');
    $container.empty();
    
    if (notifications.length === 0) {
        $container.html('<p class="text-center text-gray">No notifications</p>');
        return;
    }
    
    notifications.forEach(function(notification) {
        $container.append(`
            <a href="${notification.link}" class="notification-item ${notification.is_read ? '' : 'unread'}">
                <i class="${notification.icon}"></i>
                <div class="notification-content">
                    <p class="notification-title">${notification.title}</p>
                    <span class="notification-time">${notification.time_ago}</span>
                </div>
            </a>
        `);
    });
}

// ============================================
// Sidebar Functions
// ============================================
function initializeSidebar() {
    // Active link highlighting
    const currentPath = window.location.pathname;
    $('.sidebar__link, .sidebar__category').each(function() {
        const href = $(this).attr('href');
        if (href && currentPath.includes(href)) {
            $(this).addClass('active');
        }
    });
    
    // Close sidebar on overlay click (mobile)
    $('.sidebar-overlay').on('click', function() {
        $('.sidebar').removeClass('open');
        $('body').removeClass('sidebar-open');
    });
}

// ============================================
// Alert Functions
// ============================================
function initializeAlerts() {
    // Auto-dismiss alerts after 5 seconds
    $('.alert[data-auto-dismiss]').each(function() {
        const $alert = $(this);
        setTimeout(function() {
            $alert.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    });
    
    // Manual dismiss
    $('.alert__close').on('click', function() {
        $(this).closest('.alert').fadeOut(300, function() {
            $(this).remove();
        });
    });
}

function showAlert(message, type = 'info') {
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    
    const $alert = $(`
        <div class="alert alert--${type}" data-auto-dismiss>
            <i class="${icons[type]}"></i>
            <span>${message}</span>
            <button class="alert__close"><i class="fas fa-times"></i></button>
        </div>
    `);
    
    $('.alert-container').prepend($alert);
    
    setTimeout(function() {
        $alert.fadeOut(300, function() {
            $(this).remove();
        });
    }, 5000);
}

// ============================================
// Form Functions
// ============================================
function initializeForms() {
    // Form validation
    $('form[data-validate]').on('submit', function(e) {
        const $form = $(this);
        let isValid = true;
        
        // Check required fields
        $form.find('[required]').each(function() {
            const $field = $(this);
            if (!$field.val().trim()) {
                isValid = false;
                showFieldError($field, 'This field is required');
            } else {
                clearFieldError($field);
            }
        });
        
        // Check email fields
        $form.find('input[type="email"]').each(function() {
            const $field = $(this);
            if ($field.val() && !isValidEmail($field.val())) {
                isValid = false;
                showFieldError($field, 'Please enter a valid email');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Clear error on input
    $('input, textarea, select').on('input change', function() {
        clearFieldError($(this));
    });
    
    // Character counter for textareas
    $('textarea[data-max-length]').each(function() {
        const $textarea = $(this);
        const maxLength = parseInt($textarea.data('max-length'));
        
        $textarea.after(`<div class="char-counter"><span>0</span>/${maxLength}</div>`);
        
        $textarea.on('input', function() {
            const length = $(this).val().length;
            $(this).next('.char-counter').find('span').text(length);
            
            if (length > maxLength) {
                $(this).next('.char-counter').addClass('exceeded');
            } else {
                $(this).next('.char-counter').removeClass('exceeded');
            }
        });
    });
    
    // File input preview
    $('input[type="file"][data-preview]').on('change', function() {
        const $input = $(this);
        const $preview = $($input.data('preview'));
        const file = this.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $preview.attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
}

function showFieldError($field, message) {
    $field.addClass('error');
    const $error = $field.next('.field-error');
    if ($error.length) {
        $error.text(message);
    } else {
        $field.after(`<span class="field-error">${message}</span>`);
    }
}

function clearFieldError($field) {
    $field.removeClass('error');
    $field.next('.field-error').remove();
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ============================================
// Modal Functions
// ============================================
function initializeModals() {
    // Open modal
    $('[data-modal]').on('click', function(e) {
        e.preventDefault();
        const modalId = $(this).data('modal');
        openModal(modalId);
    });
    
    // Close modal on overlay click
    $('.modal').on('click', function(e) {
        if (e.target === this) {
            closeModal($(this).attr('id'));
        }
    });
    
    // Close modal on close button
    $('.modal__close').on('click', function() {
        closeModal($(this).closest('.modal').attr('id'));
    });
    
    // Close modal on escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.modal.open').each(function() {
                closeModal($(this).attr('id'));
            });
        }
    });
}

function openModal(id) {
    $(`#${id}`).addClass('open');
    $('body').addClass('modal-open');
}

function closeModal(id) {
    $(`#${id}`).removeClass('open');
    if (!$('.modal.open').length) {
        $('body').removeClass('modal-open');
    }
}

// ============================================
// Post Functions
// ============================================
function initializePosts() {
    // Like button
    $(document).on('click', '.post-card__action.like-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const postId = $btn.data('post-id');
        
        $.ajax({
            url: 'api/posts.php',
            method: 'POST',
            data: { action: 'like', post_id: postId },
            success: function(response) {
                if (response.success) {
                    $btn.toggleClass('liked');
                    const $count = $btn.find('.count');
                    const currentCount = parseInt($count.text()) || 0;
                    $count.text(response.liked ? currentCount + 1 : currentCount - 1);
                }
            }
        });
    });
    
    // Infinite scroll for posts
    if ($('.posts-feed').length) {
        let page = 1;
        let loading = false;
        
        $(window).on('scroll', function() {
            if (loading) return;
            
            const scrollPos = $(window).scrollTop() + $(window).height();
            const docHeight = $(document).height();
            
            if (scrollPos >= docHeight - 200) {
                loading = true;
                loadMorePosts(++page).then(() => {
                    loading = false;
                });
            }
        });
    }
}

function loadMorePosts(page) {
    return $.ajax({
        url: 'api/posts.php',
        method: 'GET',
        data: { page: page },
        success: function(response) {
            if (response.success && response.data.items.length) {
                response.data.items.forEach(function(post) {
                    $('.posts-feed').append(renderPostCard(post));
                });
            }
        }
    });
}

function renderPostCard(post) {
    return `
        <article class="post-card">
            <div class="post-card__header">
                <img src="${post.avatar || 'assets/images/default-avatar.png'}" alt="${post.full_name}" class="post-card__avatar">
                <div class="post-card__author">
                    <h4>${post.full_name}</h4>
                    <span>${post.time_ago}</span>
                </div>
                <span class="post-card__category" style="background: ${post.category_color}">${post.category_name}</span>
            </div>
            <div class="post-card__content">
                <h3><a href="post.php?slug=${post.slug}">${post.title}</a></h3>
                <p>${post.excerpt}</p>
            </div>
            ${post.media ? `<div class="post-card__media"><img src="uploads/posts/${post.media}" alt=""></div>` : ''}
            <div class="post-card__actions">
                <button class="post-card__action like-btn" data-post-id="${post.id}">
                    <i class="far fa-heart"></i> <span class="count">${post.likes_count}</span>
                </button>
                <a href="post.php?slug=${post.slug}#comments" class="post-card__action">
                    <i class="far fa-comment"></i> <span>${post.comments_count}</span>
                </a>
                <button class="post-card__action share-btn" data-url="${window.location.origin}/post.php?slug=${post.slug}">
                    <i class="far fa-share-square"></i> Share
                </button>
            </div>
        </article>
    `;
}

// ============================================
// Tooltip Functions
// ============================================
function initializeTooltips() {
    $('[data-tooltip]').each(function() {
        const $el = $(this);
        const text = $el.data('tooltip');
        
        $el.on('mouseenter', function() {
            const $tooltip = $('<div class="tooltip">' + text + '</div>');
            $('body').append($tooltip);
            
            const elOffset = $el.offset();
            const elWidth = $el.outerWidth();
            const tooltipWidth = $tooltip.outerWidth();
            
            $tooltip.css({
                top: elOffset.top - $tooltip.outerHeight() - 8,
                left: elOffset.left + (elWidth / 2) - (tooltipWidth / 2)
            });
        });
        
        $el.on('mouseleave', function() {
            $('.tooltip').remove();
        });
    });
}

// ============================================
// Utility Functions
// ============================================
function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showAlert('Copied to clipboard!', 'success');
    }).catch(function() {
        showAlert('Failed to copy', 'error');
    });
}

// AJAX Setup
$.ajaxSetup({
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    }
});
