import ApiStore from '../lib/ApiStore';
import CRUD from './CRUD.svelte';

export default {
  title: 'Example/CRUD',
  component: CRUD,
  argTypes: {
    create: { action: 'create' },
    edit: { action: 'edit' },
    delete: { action: 'delete' },
  },
};

const Template = (args) => ({
  Component: CRUD,
  props: args,
  on: {
    create: args.create,
    edit: args.edit,
    delete: args.delete,
  },
});

export const PreguntasApi = Template.bind({});
PreguntasApi.args = {
  config: {
    headers: [
      {
        label: 'ID',
        value: 'id',
        sortBy: 'id',
        align: 'center'
      },
      {
        label: 'ID Grupo',
        value: 'attributes.id_grupo',
        sortBy: 'id_grupo',
        align: 'center'
      },
      {
        label: 'Indice',
        value: 'attributes.indice',
        sortBy: 'indice',
        align: 'left'
      },
      {
        label: 'Descripción',
        value: 'attributes.descripcion',
        sortBy: 'descripcion',
        align: 'left',
        width: '100%',
      },
      {
        label: '',
        value: '["edit"]',
        sortBy: '',
        align: 'center',
        control: 'actions',
      },
    ],
    sort: {
      field: 'id',
      order: 'asc'
    }
  },
  store: new ApiStore({
    url: 'http://localhost/projects/callizaya2/public/api.php/ob3/preguntas',
    root: 'data',
    limit: 20,
    query: {
      per_page: (/** @type {{ limit: number; }} */{ limit }) => limit,
      page: (/** @type {{ offset: number; limit: number; }} */{ offset, limit }) => offset / limit + 1,
      sort: (/** @type {{ sort_field: string; sort_direction: string; }} */{ sort_field, sort_direction }) => (sort_direction === 'desc' ? '-' : '') + sort_field,
      filter: ['tipo_credito(2)']
    },
  }),
  create: [
    [
      {
        control: 'TextBox',
        type: 'number',
        name: 'id',
        placeholder: 'id',
      },
    ],
    [
      {
        control: 'TextBox',
        type: 'number',
        name: 'attributes.id_grupo',
        placeholder: 'id_grupo',
      },
    ],
    [
      {
        control: 'TextBox',
        type: 'text',
        name: 'attributes.indice',
        placeholder: 'indice',
      },
    ],
    [
      {
        control: 'TextArea',
        type: 'text',
        name: 'attributes.descripcion',
        placeholder: 'descripcion',
        rows: 4,
      },
    ],
  ],
};
