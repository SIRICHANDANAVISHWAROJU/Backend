// require('dotenv').config();

// const express = require('express');
// const mongoose = require('mongoose');
// const Product = require('./models/product.model');
// const app = express();
// const PORT = process.env.PORT || 3000;

// // Middleware
// app.use(express.json());

// // MongoDB Connection
// mongoose.connect(process.env.MONGO_URI2)
// .then(() => {
//     console.log("Connected to MongoDB");
// })
// .catch((err) => {
//     console.log("MongoDB Error:", err);
// });

// // Routes
// app.get('/', (req, res) => {
//     res.send("Hello World");
// });

// app.post('/api/products', async (req, res) => {
//     try {
//         const product = await Product.create(req.body);
//         res.status(201).json(product);
//     } catch (error) {
//         res.status(500).json({ message: error.message });
//     }
// });

// app.get('/api/products', async (req, res) => {
//     try {
//         const products = await Product.find({});
//         res.status(200).json(products);
//     } catch (error) {
//         res.status(500).json({ message: error.message });
//     }
// });

// app.get('/api/products/:id', async (req, res) => {
//     try {
//         const product=await Product.findById(req.params.id);
//         if (!product) {
//             return res.status(404).json({ message: 'Product not found' });
//         }
//         res.status(200).json(product);
//     } catch (error) {
//         console.error("Error handling GET request:",err.error); 
//         res.status(500).json({error: 'Internal Server Error' });
//     }
// });

// app.delete('/api/products/:id', async (req, res) => {
//     try {
//         const product = await Product.findByIdAndDelete(req.params.id);
//         if (!product) {
//             return res.status(404).json({ message: 'Product not found' });
//         }
//         res.status(200).json({ message: 'Product deleted successfully' });
//     } catch (error) {
//         console.error("Error handling DELETE request:", error);
//         res.status(500).json({ error: 'Internal Server Error' });
//         }
    
// });

// app.put('/api/products/:id', async (req, res) => {
//     try {
//         const product = await Product.findByIdAndUpdate(req.params.id, req.body);
//         if (!product) {
//             return res.status(404).json({ message: 'Product not found' });
//         }
//         const updatedProduct = await Product.findById(req.params.id);
//         res.status(200).json(updatedProduct);
//     } catch (error) {
//         console.error("Error handling PUT request:", error.message);
//         res.status(500).json({ error: 'Internal Server Error' });
//     }
// });

// // Server start
// app.listen(PORT, () => {
//     console.log(`Server running on port ${PORT}`);
// });



const dotenv = require('dotenv').config();
const express = require('express');
const productRoutes = require('./routes/product.route.js');
const connectDB = require('./config/db.js');

const PORT = process.env.PORT || 3000;
const app = express();

//Middleware
app.use(express.json());
app.use(express.urlencoded({ extended: false }));

//connect to MongoDB
connectDB();

//Routes
app.use('/api/products', productRoutes);

//start server
app.listen(PORT, () => {
    console.log(`Server running on port http://localhost${PORT}`);
});