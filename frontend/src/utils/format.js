/**
 * Single source of truth for money formatting across the app.
 * Replaces the scattered `€{{ Number(x || 0).toFixed(2) }}` expressions and the
 * ad-hoc Intl.NumberFormat that lived in ProductCard.
 */
export function formatMoney(value) {
	return new Intl.NumberFormat('en-IE', {
		style: 'currency',
		currency: 'EUR',
	}).format(Number(value || 0))
}
