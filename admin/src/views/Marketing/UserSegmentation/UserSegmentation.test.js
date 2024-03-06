import React from 'react';
import ReactDOM from 'react-dom';
import UserSegmentation from './UserSegmentation';
import { shallow } from 'enzyme'


it('renders without crashing', () => {
  const div = document.createElement('div');
  ReactDOM.render(<UserSegmentation />, div);
  ReactDOM.unmountComponentAtNode(div);
});

it('renders without crashing', () => {
  shallow(<UserSegmentation />);
});
