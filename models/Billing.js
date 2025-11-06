const mongoose = require('mongoose');

const billingSchema = new mongoose.Schema({
    firstName: String,
    lastName: String,
    phoneNumber: String,
    email: String,
    address: String,
    address2: String,
    country: String,
    state: String,
    zip: String,
});

module.exports = mongoose.model('Billing', billingSchema);