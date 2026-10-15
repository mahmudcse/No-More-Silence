const banglaDigits: Record<string, string> = {
    '0': '০',
    '1': '১',
    '2': '২',
    '3': '৩',
    '4': '৪',
    '5': '৫',
    '6': '৬',
    '7': '৭',
    '8': '৮',
    '9': '৯',
    '.': '.',
};

export function toBanglaNumber(
    value: number | string | null | undefined,
): string {
    if (value === null || value === undefined) {
        return '';
    }

    return String(value)
        .split('')
        .map((char) => banglaDigits[char] ?? char)
        .join('');
}

export function formatNumberByLocale(
    value: number | string | null | undefined,
    locale: string = 'bn',
): string {
    if (locale === 'bn') {
        return toBanglaNumber(value);
    }
    return String(value ?? '');
}

export function formatDateByLocale(
    dateString: string | null | undefined,
    locale: string = 'bn',
): string {
    if (!dateString) {
        return '';
    }

    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return dateString;
        }

        if (locale === 'bn') {
            const formatted = new Intl.DateTimeFormat('bn-BD', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            }).format(date);
            return formatted;
        }

        return new Intl.DateTimeFormat('en-GB', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        }).format(date);
    } catch {
        return dateString;
    }
}
