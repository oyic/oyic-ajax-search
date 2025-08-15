/**
 * OYIC Ajax Search JavaScript - Vanilla JS Version
 */
(function() {
    'use strict';
    
    class OYICAjaxSearch {
        constructor() {
            this.modal = null;
            this.searchInput = null;
            this.resultsContainer = null;
            this.loadingEl = null;
            this.searchTimeout = null;
            this.currentRequest = null;
            
            this.init();
        }
        
        init() {
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.setupElements());
            } else {
                this.setupElements();
            }
        }
        
        setupElements() {
            this.modal = document.getElementById('oyic-search-modal');
            this.searchInput = document.getElementById('oyic-search-input');
            this.resultsContainer = document.querySelector('.oyic-search-results-list');
            this.loadingEl = document.querySelector('.oyic-search-loading');
            
            // CRITICAL: Force modal to be hidden on initialization
            if (this.modal) {
                // Remove any existing classes that might show the modal
                this.modal.classList.remove('oyic-fade-in', 'oyic-fade-out');
                
                // Force display none with !important equivalent
                this.modal.style.setProperty('display', 'none', 'important');
                this.modal.style.setProperty('visibility', 'hidden', 'important');
                this.modal.style.setProperty('opacity', '0', 'important');
                
                // Add a data attribute to track initialization
                this.modal.setAttribute('data-oyic-initialized', 'true');
                
                console.log('OYIC Search: Modal forcibly hidden on initialization');
            } else {
                console.warn('OYIC Search: Modal element not found during initialization');
            }
            
            this.bindEvents();
            
            // Failsafe: Check for modal visibility every 100ms for first 2 seconds
            this.setupFailsafe();
        }
        
        setupFailsafe() {
            let checkCount = 0;
            const maxChecks = 20; // 2 seconds worth of checks
            
            const intervalId = setInterval(() => {
                if (this.modal && this.isModalUnintentionallyVisible()) {
                    console.warn('OYIC Search: Detected unwanted modal visibility, forcing close');
                    this.forceCloseModal();
                }
                
                checkCount++;
                if (checkCount >= maxChecks) {
                    clearInterval(intervalId);
                }
            }, 100);
        }
        
        isModalUnintentionallyVisible() {
            if (!this.modal) return false;
            
            const computedStyle = window.getComputedStyle(this.modal);
            const isVisible = computedStyle.display !== 'none' && 
                             computedStyle.visibility !== 'hidden' && 
                             computedStyle.opacity !== '0';
            
            // If modal is visible but we didn't intentionally open it
            return isVisible && !this.modal.hasAttribute('data-oyic-opened');
        }
        
        forceCloseModal() {
            if (!this.modal) return;
            
            console.log('OYIC Search: Force closing modal');
            
            // Immediately hide without animation
            this.modal.style.setProperty('display', 'none', 'important');
            this.modal.style.setProperty('visibility', 'hidden', 'important');
            this.modal.style.setProperty('opacity', '0', 'important');
            this.modal.classList.remove('oyic-fade-in', 'oyic-fade-out');
            this.modal.removeAttribute('data-oyic-opened');
            
            document.body.classList.remove('oyic-search-open');
            document.body.style.overflow = '';
        }
        
        bindEvents() {
            // Search trigger buttons - using event delegation
            document.addEventListener('click', (e) => {
                if (e.target.matches('.oyic-search-trigger') || e.target.closest('.oyic-search-trigger')) {
                    e.preventDefault();
                    this.openModal();
                }
            });
            
            // Close modal events
            document.addEventListener('click', (e) => {
                if (e.target.matches('.oyic-search-close')) {
                    e.preventDefault();
                    this.closeModal();
                } else if (e.target.matches('.oyic-search-overlay')) {
                    this.closeModal();
                }
            });
            
            // Search input
            if (this.searchInput) {
                this.searchInput.addEventListener('input', (e) => {
                    this.handleSearch(e.target.value);
                });
            }
            
            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                // Ctrl/Cmd + K to open search
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    this.openModal();
                }
                
                // Escape to close modal
                if (e.key === 'Escape' && this.modal && this.isModalVisible()) {
                    this.closeModal();
                }
            });
            
            // Result item clicks - using event delegation
            document.addEventListener('click', (e) => {
                if (e.target.matches('.oyic-search-result-item') || e.target.closest('.oyic-search-result-item')) {
                    this.closeModal();
                }
            });
        }
        
        isModalVisible() {
            return this.modal && this.modal.style.display !== 'none' && 
                   window.getComputedStyle(this.modal).display !== 'none';
        }
        
        openModal() {
            if (!this.modal) {
                console.error('OYIC Search: Cannot open modal - element not found');
                return;
            }
            
            console.log('OYIC Search: Opening modal');
            
            // Remove all previous classes and forced styles
            this.modal.classList.remove('oyic-fade-out');
            this.modal.style.removeProperty('display');
            this.modal.style.removeProperty('visibility');
            this.modal.style.removeProperty('opacity');
            
            // Set proper display and visibility
            this.modal.style.setProperty('display', 'flex', 'important');
            this.modal.style.setProperty('visibility', 'visible', 'important');
            this.modal.style.setProperty('opacity', '1', 'important');
            
            // Mark as intentionally opened
            this.modal.setAttribute('data-oyic-opened', 'true');
            
            // Apply custom overlay settings if available
            if (typeof oyic_ajax_search !== 'undefined' && oyic_ajax_search.settings) {
                const overlay = this.modal.querySelector('.oyic-search-overlay');
                if (overlay) {
                    if (oyic_ajax_search.settings.overlay_bg) {
                        overlay.style.background = oyic_ajax_search.settings.overlay_bg;
                    }
                    if (oyic_ajax_search.settings.overlay_opacity) {
                        overlay.style.opacity = oyic_ajax_search.settings.overlay_opacity;
                    }
                }
            }
            
            // Force a reflow and add animation class
            this.modal.offsetHeight;
            this.modal.classList.add('oyic-fade-in');
            
            if (this.searchInput) {
                setTimeout(() => {
                    this.searchInput.focus();
                }, 100);
            }
            
            document.body.classList.add('oyic-search-open');
            // Disable body scroll
            document.body.style.overflow = 'hidden';
        }
        
        closeModal() {
            if (!this.modal) {
                console.warn('OYIC Search: Cannot close modal - element not found');
                return;
            }
            
            console.log('OYIC Search: Closing modal');
            
            this.modal.classList.remove('oyic-fade-in');
            this.modal.classList.add('oyic-fade-out');
            
            setTimeout(() => {
                // Force modal to be completely hidden
                this.modal.style.setProperty('display', 'none', 'important');
                this.modal.style.setProperty('visibility', 'hidden', 'important');
                this.modal.style.setProperty('opacity', '0', 'important');
                
                this.modal.classList.remove('oyic-fade-in', 'oyic-fade-out');
                this.modal.removeAttribute('data-oyic-opened');
                
                if (this.searchInput) {
                    this.searchInput.value = '';
                }
                
                if (this.resultsContainer) {
                    this.resultsContainer.innerHTML = '';
                }
                
                if (this.loadingEl) {
                    this.loadingEl.style.display = 'none';
                }
                
                document.body.classList.remove('oyic-search-open');
                // Re-enable body scroll
                document.body.style.overflow = '';
                
                // Cancel any pending request
                if (this.currentRequest) {
                    this.currentRequest.abort();
                    this.currentRequest = null;
                }
                
                console.log('OYIC Search: Modal closed and hidden');
            }, 200);
        }
        
        handleSearch(query) {
            // Clear previous timeout
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }
            
            // Cancel previous request
            if (this.currentRequest) {
                this.currentRequest.abort();
                this.currentRequest = null;
            }
            
            // Clear results if query is empty
            if (!query.trim()) {
                if (this.resultsContainer) {
                    this.resultsContainer.innerHTML = '';
                }
                this.hideLoading();
                return;
            }
            
            // Debounce search
            this.searchTimeout = setTimeout(() => {
                this.performSearch(query);
            }, 300);
        }
        
        performSearch(query) {
            this.showLoading();
            
            // Check if oyic_ajax_search is available
            if (typeof oyic_ajax_search === 'undefined') {
                this.hideLoading();
                this.displayError('Search configuration not found');
                return;
            }
            
            // Create FormData for the request
            const formData = new FormData();
            formData.append('action', 'oyic_ajax_search');
            formData.append('search_term', query);
            formData.append('nonce', oyic_ajax_search.nonce);
            
            // Create XMLHttpRequest
            this.currentRequest = new XMLHttpRequest();
            this.currentRequest.timeout = 10000; // 10 second timeout
            
            this.currentRequest.onload = () => {
                this.hideLoading();
                
                if (this.currentRequest.status === 200) {
                    try {
                        const response = JSON.parse(this.currentRequest.responseText);
                        
                        if (response.success && response.data) {
                            this.displayResults(response.data.results || [], query);
                        } else {
                            this.displayError(response.data?.message || 'Search failed');
                        }
                    } catch (e) {
                        this.displayError('Invalid response from server');
                    }
                } else {
                    this.handleRequestError(this.currentRequest.status);
                }
                
                this.currentRequest = null;
            };
            
            this.currentRequest.onerror = () => {
                this.hideLoading();
                this.displayError('Network error occurred. Please check your connection.');
                this.currentRequest = null;
            };
            
            this.currentRequest.ontimeout = () => {
                this.hideLoading();
                this.displayError('Request timed out. Please try again.');
                this.currentRequest = null;
            };
            
            this.currentRequest.onabort = () => {
                // Request was cancelled, do nothing
                this.currentRequest = null;
            };
            
            // Send request
            this.currentRequest.open('POST', oyic_ajax_search.ajax_url, true);
            this.currentRequest.send(formData);
        }
        
        handleRequestError(status) {
            let message;
            
            switch (status) {
                case 403:
                    message = 'Access denied. Please refresh the page and try again.';
                    break;
                case 500:
                    message = 'Server error occurred. Please try again later.';
                    break;
                case 404:
                    message = 'Search endpoint not found.';
                    break;
                default:
                    message = 'Network error occurred. Please check your connection.';
            }
            
            this.displayError(message);
        }
        
        showLoading() {
            if (this.loadingEl) {
                this.loadingEl.style.display = 'block';
            }
            if (this.resultsContainer) {
                this.resultsContainer.innerHTML = '';
            }
        }
        
        hideLoading() {
            if (this.loadingEl) {
                this.loadingEl.style.display = 'none';
            }
        }
        
        displayResults(results, query) {
            if (!this.resultsContainer) return;
            
            this.resultsContainer.innerHTML = '';
            
            if (!results || results.length === 0) {
                this.displayNoResults(query);
                return;
            }
            
            results.forEach(result => {
                const resultEl = this.createResultElement(result, query);
                this.resultsContainer.appendChild(resultEl);
            });
        }
        
        createResultElement(result, query) {
            const highlightedTitle = this.highlightText(result.title, query);
            const highlightedExcerpt = this.highlightText(result.excerpt, query);
            
            const resultEl = document.createElement('a');
            resultEl.href = result.permalink;
            resultEl.className = 'oyic-search-result-item';
            
            resultEl.innerHTML = `
                <h3 class="oyic-search-result-title">${highlightedTitle}</h3>
                <p class="oyic-search-result-excerpt">${highlightedExcerpt}</p>
                <div class="oyic-search-result-meta">${this.formatPostType(result.post_type)}</div>
            `;
            
            return resultEl;
        }
        
        highlightText(text, query) {
            if (!text || !query) return text;
            
            const regex = new RegExp(`(${this.escapeRegex(query)})`, 'gi');
            return text.replace(regex, '<mark>$1</mark>');
        }
        
        escapeRegex(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }
        
        formatPostType(postType) {
            const types = {
                'post': 'Blog Post',
                'page': 'Page',
                'product': 'Product',
                'event': 'Event'
            };
            
            return types[postType] || postType.charAt(0).toUpperCase() + postType.slice(1);
        }
        
        displayNoResults(query) {
            if (!this.resultsContainer) return;
            
            const noResultsEl = document.createElement('div');
            noResultsEl.className = 'oyic-search-no-results';
            noResultsEl.innerHTML = `${oyic_ajax_search.strings.no_results} "${this.escapeHtml(query)}"`;
            
            this.resultsContainer.appendChild(noResultsEl);
        }
        
        displayError(message) {
            if (!this.resultsContainer) return;
            
            const errorEl = document.createElement('div');
            errorEl.className = 'oyic-search-no-results';
            errorEl.innerHTML = `<strong>Error:</strong> ${this.escapeHtml(message)}`;
            
            this.resultsContainer.appendChild(errorEl);
        }
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }
    
    // Dark mode handler
    class DarkModeHandler {
        constructor() {
            this.init();
        }
        
        init() {
            // Check for saved theme preference or default to light mode
            const savedTheme = localStorage.getItem('oyic-search-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                this.enableDarkMode();
            } else {
                this.enableLightMode();
            }
            
            // Listen for system theme changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (!localStorage.getItem('oyic-search-theme')) {
                    if (e.matches) {
                        this.enableDarkMode();
                    } else {
                        this.enableLightMode();
                    }
                }
            });
        }
        
        enableDarkMode() {
            document.documentElement.classList.add('dark');
            localStorage.setItem('oyic-search-theme', 'dark');
        }
        
        enableLightMode() {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('oyic-search-theme', 'light');
        }
        
        toggle() {
            if (document.documentElement.classList.contains('dark')) {
                this.enableLightMode();
            } else {
                this.enableDarkMode();
            }
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new OYICAjaxSearch();
            new DarkModeHandler();
        });
    } else {
        new OYICAjaxSearch();
        new DarkModeHandler();
    }
    
    // Global helper function to add search button
    window.oyicAddSearchButton = function(selector, text = 'Search') {
        const elements = document.querySelectorAll(selector);
        elements.forEach(element => {
            const button = document.createElement('button');
            button.className = 'oyic-search-trigger';
            button.textContent = text;
            element.appendChild(button);
        });
    };
    
})();