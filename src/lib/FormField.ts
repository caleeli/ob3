import type ApiStore from "./ApiStore";
import type StoreInterface from "./StoreInterface";

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
    public name?= '';
    public label?= '';
    public placeholder?= '';
    public variant?: 'standard' | 'accent' | 'hyperlink';
    public options?: {
        value: any;
        name: string;
        disabled?: boolean;
    }[];
    public rows?: number;
    public action?: (() => Promise<void>) | ((value?: any) => Promise<void>);
    public store?: StoreInterface | ApiStore;
    public storeValueField?: string;
    public storeNameField?: string;
    public storeDisabledField?: string;
    public getter?: (field: any) => any;
    public setter?: (field: any, value: any) => any;
    // internal properties
    public actionInProgress?: boolean = false;
    public searchValue?: string = '';
}
export default FormField;
