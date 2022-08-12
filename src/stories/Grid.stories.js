import ApiStore from '../lib/ApiStore';
import ArrayStore from '../lib/ArrayStore';
import Grid from './Grid.svelte';

export default {
  title: 'Example/Grid',
  component: Grid,
  argTypes: {
    edit: { action: 'edit' },
    delete: { action: 'delete' },
  },
};

const Template = (args) => ({
  Component: Grid,
  props: args,
  on: {
    edit: args.edit,
    delete: args.delete,
  },
});

export const FromArray = Template.bind({});
FromArray.args = {
  config: {
    headers: [
      {
        label: 'Name',
        value: 'attributes.name',
        sortBy: 'attributes.name',
        align: 'left'
      },
      {
        label: 'Age',
        value: 'attributes.age',
        sortBy: 'attributes.age',
        align: 'center'
      },
      {
        label: 'Double Age',
        value: 'attributes.age*2',
        sortBy: 'attributes.age',
        align: 'center'
      },
      {
        label: 'Amount',
        value: 'Math.random()*1000',
        format: ['currency'],
        align: 'right'
      },
      {
        label: '',
        value: '["edit", "delete"]',
        control: 'actions',
        align: 'center'
      }
    ],
    sort: {
      field: '',
      order: 'asc'
    }
  },
  store: new ArrayStore([
    { attributes: { name: 'Jimmy Mcgill', age: 45 } },
    { attributes: { name: 'Kim Wexler', age: 40 } },
    { attributes: { name: 'Ignacio Varga', age: 37 } }
  ]),
};

export const FromApi = Template.bind({});
FromApi.args = {
  config: {
    headers: [
      {
        label: 'ID',
        value: 'id',
        sortBy: 'id',
        align: 'left'
      },
      {
        label: 'First Name',
        value: 'first_name',
        sortBy: 'first_name',
        align: 'left'
      },
      {
        label: 'Last name',
        value: 'last_name',
        sortBy: 'last_name',
        align: 'left'
      },
      {
        label: 'Phone Number',
        value: 'phone_number',
        align: 'right'
      },
      {
        label: '',
        value: '["edit", "delete"]',
        control: 'actions',
        align: 'center'
      }
    ],
    sort: {
      field: '',
      order: 'asc'
    }
  },
  store: new ApiStore({
    url: 'https://random-data-api.com/api/users/random_user?size=5',
  }),
};


export const PokemonApi = Template.bind({});
PokemonApi.args = {
  config: {
    headers: [
      {
        label: 'Name',
        value: 'name',
        sortBy: 'name',
        align: 'left'
      },
      {
        label: 'URL',
        value: 'url',
        sortBy: 'url',
        align: 'left'
      },
    ],
    sort: {
      field: '',
      order: 'asc'
    }
  },
  store: new ApiStore({
    url: 'https://pokeapi.co/api/v2/pokemon',
    root: 'results',
    query: {
      limit: 15,
      offset: (/** @type {{ offset: number; }} */{ offset }) => offset,
    },
  }),
};
