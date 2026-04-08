const Product = require('../models/product.model');

const createProduct = async(req, res) => {
    try {
        const product = await Product.create(req.body);
        res.status(201).json(product);
    } catch (error) {
        res.status(500).json({ message: error.message });
    }
};
const getProduct = async(req, res) => {
    try {
        const products = await Product.find({});
        res.status(200).json(products);
    } catch (error) {
        res.status(500).json({ message: error.message });
    }
};
const geProductById = async(req, res) => {
    try {
        const product=await Product.findById(req.params.id);
        if (!product) {
            return res.status(404).json({ message: 'Product not found' });
        }
        res.status(200).json(product);
    } catch (error) {
        console.error("Error handling GET request:",error.message); 
        res.status(500).json({error: 'Internal Server Error' });
    }
};

// Controller function to search products by name
const searchProductByName = async (req, res) => {
    const { name } = req.query;

    if (!name) {
        return res.status(400).json({ message: 'Name query parameter is required' });
    }

    try {
        const products = await Product.find({ name: { $regex: name, $options: 'i' } }); // Case-insensitive search
        res.json(products);
    } catch (error) {
        res.status(500).json({ message: 'Server error', error: error.message });
    }
};

const updateProduct = async(req, res) => {
    try {
        const product = await Product.findByIdAndUpdate(req.params.id, req.body);
        if (!product) {
            return res.status(404).json({ message: 'Product not found' });
        }
        const updatedProduct = await Product.findById(req.params.id);
        res.status(200).json(updatedProduct);
    } catch (error) {
        console.error("Error handling PUT request:", error.message);
        res.status(500).json({ error: 'Internal Server Error' });
    }
};
const deleteProduct = async(req, res) => {
    try {
        const product = await Product.findByIdAndDelete(req.params.id);
        if (!product) {
            return res.status(404).json({ message: 'Product not found' });
        }
        res.status(200).json({ message: 'Product deleted successfully' });
    } catch (error) {
        console.error("Error handling DELETE request:", error);
        res.status(500).json({ error: 'Internal Server Error' });
        }
};

module.exports = {
    createProduct,
    getProduct,
    geProductById,
    updateProduct,
    deleteProduct,
    searchProductByName
};