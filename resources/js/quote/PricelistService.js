/**
 * PricelistService - Handles API calls for Pricelist operations
 */
export class PricelistService {
    constructor(baseUrl = '/api/pricelists') {
        this.baseUrl = baseUrl;
    }

    /**
     * Search pricelists by query
     * @param {string} query - Search term
     * @param {number|null} priceTypeId - Filter by price type
     * @param {number} limit - Max results
     * @returns {Promise<Array>}
     */
    async search(query, priceTypeId = null, limit = 30) {
        if (!query || query.length < 2) {
            return [];
        }

        const params = new URLSearchParams({
            q: query,
            limit: limit.toString(),
        });

        if (priceTypeId) {
            params.append('price_type_id', priceTypeId.toString());
        }

        try {
            const response = await fetch(`${this.baseUrl}/search?${params}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('PricelistService.search error:', error);
            return [];
        }
    }

    /**
     * Get all price types
     * @returns {Promise<Array>}
     */
    async getPriceTypes() {
        try {
            const response = await fetch(`${this.baseUrl}/price-types`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('PricelistService.getPriceTypes error:', error);
            return [];
        }
    }
}

// Export singleton instance
export const pricelistService = new PricelistService();
