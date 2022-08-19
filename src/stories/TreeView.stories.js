import ApiStore from '../lib/ApiStore';
import { within, userEvent } from '@storybook/testing-library';
import TreeView from './TreeView.svelte';

export default {
  title: 'Example/TreeView',
  component: TreeView,
  parameters: {
  },
};

const Template = (args) => ({
  Component: TreeView,
  props: args,
});

export const Simple = Template.bind({});
Simple.args = {
  store: new ApiStore({
    url: 'http://localhost/projects/callizaya2/public/api.php/ob3/menus',
    root: 'data',
    query: {
      per_page: 200,
    },
  }),
};
