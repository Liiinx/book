/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
// import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.scss';

// console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

import Duck from './duck.js';

const duck = new Duck('Waddles');
duck.quack();

// import { Alert } from 'bootstrap';
import 'bootstrap';