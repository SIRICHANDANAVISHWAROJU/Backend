const express = require('express');
const router = express.Router();

const {createProduct, getProduct, geProductById, updateProduct, deleteProduct, searchProductByName} 
= require('../controllers/product.controller');

router.post('/', createProduct);
router.get('/', getProduct);
router.get('/search', searchProductByName);
router.get('/:id', geProductById);
router.put('/:id', updateProduct);
router.delete('/:id', deleteProduct);

module.exports = router;