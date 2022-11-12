import ApiStore from "./ApiStore";
import ConfigStore from "./ConfigStore";
import FormField from "./FormField";

export default {
    content(content: any[]): FormField[][] {
        // check if content is an array of arrays
        if (!(Array.isArray(content) && content.every((row) => Array.isArray(row)))) {
            throw new Error("Content must be an array of arrays");
        }
        // Convert content to FormField[][] and return
        return content.map((row) => row.map((field: any) => new FormField(field)));
    },

    data(_config: any): any {
        return {};
    },

    store(config: any): ApiStore | undefined {
        if (config.store) {
            return new ApiStore(config.store);
        }
        return undefined;
    },

    configStore(config: { name: string; }): ConfigStore | undefined {
        if (config.name) {
            return new ConfigStore(config.name, config);
        }
        return undefined;
    },
}
