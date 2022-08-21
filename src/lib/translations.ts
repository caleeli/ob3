import es from '../lang/es.json';

const labels: { [x: string]: string; } = es;

export function translation(textOrName?: string, data: { [x: string]: any; } | undefined = undefined): string {
    if (!textOrName) {
        return '';
    }
    const translation = labels[textOrName] || textOrName;
    // replace :placeholders
    return translation.replace(/:([a-zA-Z0-9_]+)/g, (match: string, placeholder: string) => {
        return String(data[placeholder]) || match;
    });
};
