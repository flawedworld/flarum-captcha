import app from 'flarum/common/app';
import forum from './src/forum/index';

export const extend = [
  {
    extend(app) {
      forum(app);
    },
  },
];
