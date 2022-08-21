class FormField {
    public control = 'TextBox';
    public type?:
        | 'number'
        | 'search'
        | 'text'
        | 'password'
        | 'email'
        | 'tel'
        | 'url'
        | 'date'
        | 'datetime-local'
        | 'month'
        | 'time'
        | 'week' = 'text';
    public name? = '';
    public label?= '';
    public placeholder?= '';
    public variant?: 'standard' | 'accent' | 'hyperlink';
    public options?: {
        value: string;
        name: string;
    }[];
    public rows?: number;
}
export default FormField;
