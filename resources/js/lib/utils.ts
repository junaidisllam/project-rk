import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    return toUrl(urlToCheck) === currentUrl;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
export function stripTags(text: string | null | undefined): string {
    if (!text) {
        return '';
    }
    return text.replace(/<[^>]*>?/gm, '');
}

export function limitString(text: string | null | undefined, limit: number = 100): string {
    if (!text) {
        return '';
    }
    if (text.length <= limit) {
        return text;
    }
    return text.substring(0, limit) + '...';
}
