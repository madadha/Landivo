(() => {
    const catalog = document.querySelector('[data-products-catalog]');
    const grid = catalog?.querySelector('[data-products-grid]');
    const loader = catalog?.querySelector('[data-products-loader]');

    if (!catalog || !grid || !loader) {
        return;
    }

    const button = loader.querySelector('[data-load-more]');
    const status = loader.querySelector('[data-load-status]');
    const isArabic = document.documentElement.lang === 'ar';
    let loading = false;

    const setLoading = (value) => {
        loading = value;
        loader.classList.toggle('is-loading', value);
        if (button) {
            button.disabled = value;
        }
        if (status && value) {
            status.textContent = isArabic ? 'جارٍ تحميل المنتجات…' : 'Loading products…';
        }
    };

    const loadNextPage = async () => {
        const nextUrl = loader.dataset.nextUrl;
        if (loading || !nextUrl) {
            return;
        }

        setLoading(true);

        try {
            const url = new URL(nextUrl, window.location.origin);
            url.searchParams.set('products_partial', '1');
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`Request failed with status ${response.status}`);
            }

            const payload = await response.json();
            grid.insertAdjacentHTML('beforeend', payload.html ?? '');
            loader.dataset.nextUrl = payload.next_url ?? '';

            if (status) {
                status.textContent = '';
            }

            if (!payload.next_url) {
                observer?.disconnect();
                loader.remove();
            }
        } catch (error) {
            if (status) {
                status.textContent = isArabic
                    ? 'تعذر التحميل. اضغط على الزر للمحاولة مرة أخرى.'
                    : 'Could not load products. Press the button to try again.';
            }
        } finally {
            setLoading(false);
        }
    };

    button?.addEventListener('click', loadNextPage);

    const observer = 'IntersectionObserver' in window
        ? new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                loadNextPage();
            }
        }, { rootMargin: '260px 0px' })
        : null;

    observer?.observe(loader);
})();
