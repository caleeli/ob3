import ComboBox from '../lib/ComboBox.svelte';

export default {
  title: 'Example/ComboBox',
  component: ComboBox,
  argTypes: {
    select: { action: 'select' },
  },
};

const Template = (args) => ({
  Component: ComboBox,
  props: args,
  on: {
    select: args.select,
  },
});

export const Simple = Template.bind({});
Simple.args = {
};
