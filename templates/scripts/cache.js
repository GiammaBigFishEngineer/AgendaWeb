class ApiCache {
    constructor() {
        this.cache = new Map();
    }
  
    async getCachedOrFetch(url) {
        var cachedResponse = this.cache.get(url);
    
        try {
            const response = await axios.get(url, {
                headers: {
                    'If-None-Match': cachedResponse ? cachedResponse.etag : '',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            
            if (response.status === 304) {
                // Not modified, return cached response
                return cachedResponse;
            } else {
                // Update cache with new response
                this.cache.set(url, { data: response.data, etag: response.headers.etag });
                return response.data;
            }
        } catch (error) {
            if (error.response && error.response.status === 304 && cachedResponse) {
                // Not modified, return cached response
                return cachedResponse.data;
            } else {
                console.error('Error fetching data:', error);
                return null;
            }
        }
    }
}