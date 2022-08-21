import ApiStore, { JSONApiSortHandler } from '../lib/ApiStore';
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
        label: '#',
        value: 'attributes.number',
        align: 'center'
      },
      {
        label: 'Grupo',
        value: 'attributes.id_grupo',
        align: 'center'
      },
      {
        label: 'Indice',
        value: 'attributes.indice',
        align: 'left'
      },
      {
        label: 'Descripción',
        value: 'attributes.descripcion',
        align: 'left',
        width: '100%',
      },
      {
        label: '',
        value: '["edit","delete"]',
        align: 'center',
        control: 'actions',
      },
    ],
    sort: [{
      field: 'number',
      order: 'asc'
    }]
  },
  store: new ApiStore({
    url: 'http://localhost/projects/callizaya2/public/api.php/ob3/preguntas',
    root: 'data',
    limit: 20,
    query: {
      per_page: (/** @type {{ limit: number; }} */{ limit }) => limit,
      page: (/** @type {{ offset: number; limit: number; }} */{ offset, limit }) => offset / limit + 1,
      sort: JSONApiSortHandler,
      filter: ['tipo_credito(2)']
    },
  }),
  toolbar: [
    {
      icon: 'add',
      label: 'Agregar',
      action: 'add',
      initial: {
        attributes: {
          number: '',
          id_grupo: '',
          indice: '',
          descripcion: '',
          tipo_credito: 2,
        }
      },
      form: [
        [
          {
            control: 'TextBox',
            type: 'number',
            name: 'attributes.number',
            label: '# línea',
          },
        ],
        [
          {
            control: 'TextBox',
            type: 'number',
            name: 'attributes.id_grupo',
            label: 'Grupo',
          },
        ],
        [
          {
            control: 'TextBox',
            type: 'text',
            name: 'attributes.indice',
            label: 'Índice',
          },
        ],
        [
          {
            control: 'TextArea',
            type: 'text',
            name: 'attributes.descripcion',
            label: 'Descripción',
            rows: 4,
          },
        ],
      ],
    }
  ],
  rowActions: [
    {
      label: '',
      action: 'edit',
      form: [
        [
          {
            control: 'TextBox',
            type: 'number',
            name: 'attributes.number',
            label: '# línea',
          },
        ],
        [
          {
            control: 'TextBox',
            type: 'number',
            name: 'attributes.id_grupo',
            label: 'Grupo',
          },
        ],
        [
          {
            control: 'TextBox',
            type: 'text',
            name: 'attributes.indice',
            label: 'Índice',
          },
        ],
        [
          {
            control: 'TextArea',
            type: 'text',
            name: 'attributes.descripcion',
            label: 'Descripción',
            rows: 4,
          },
        ],
      ],
    }
  ],
};
