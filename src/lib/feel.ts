import { page } from '$app/stores';

export default function feel() {
	// const $GET = $page.url.searchParams.getAll();
	return {
		page,
	};
}
