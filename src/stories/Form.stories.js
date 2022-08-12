import Form from './Form.svelte';

export default {
  title: 'Example/Form',
  component: Form,
  argTypes: {
    title: { control: 'text' },
    blur: { control: 'boolean' },
  },
};

const Template = (args) => ({
  Component: Form,
  props: args,
});

export const Login = Template.bind({});
Login.args = {
  title: 'Sign In',
  content: [
    [
      {
        control: 'TextBox',
        type: 'email',
        name: 'email',
        placeholder: 'E-mail',
      },
    ],
    [
      {
        control: 'TextBox',
        type: 'password',
        name: 'password',
        placeholder: 'Password',
      },
    ],
    [
      {
        control: 'Button',
        variant: 'accent',
        label: 'Login',
      },
    ],
  ],
  blur: true,
  data: {
    email: 'default@example.com',
    password: '',
  },
};

export const User = Template.bind({});
User.args = {
  title: 'Profile',
  content: [
    [
      {
        control: 'Avatar',
        name: 'avatar',
      },
    ],
    [
      {
        control: 'Header',
        label: 'Account Settings',
      },
    ],
    [
      {
        control: 'TextBox',
        type: 'text',
        name: 'firstname',
        label: 'First Name',
      },
      {
        control: 'TextBox',
        type: 'text',
        name: 'lastname',
        label: 'Last Name',
      },
    ],
    [
      {
        control: 'TextBox',
        type: 'text',
        name: 'email',
        label: 'E-mail',
      },
    ],
    [
      {
        control: 'ComboBox',
        name: 'language',
        label: 'Language',
        options: [
          {
            name: 'English',
            value: 'en',
          },
          {
            name: 'French',
            value: 'fr',
          },
        ],
      },
    ],
    [
      {
        control: 'Button',
        variant: 'accent',
        label: 'Update Information',
      },
    ],
    [
      {
        control: 'Header',
        label: 'Change Password',
      },
    ],
    [
      {
        control: 'TextBox',
        type: 'password',
        name: 'password',
        label: 'New Password',
      },
    ],
    [
      {
        control: 'TextBox',
        type: 'password',
        name: 'confirm_password',
        label: 'Confirm Password',
      },
    ],
    [
      {
        control: 'Button',
        variant: 'accent',
        label: 'Change Password',
      },
    ],
  ],
  blur: false,
  data: {
    avatar: 'https://thispersondoesnotexist.com/image',
    firstname: 'John',
    lastname: 'Doe',
    email: 'john.doe@example.com',
    password: '',
    confirm_password: '',
  }
};
