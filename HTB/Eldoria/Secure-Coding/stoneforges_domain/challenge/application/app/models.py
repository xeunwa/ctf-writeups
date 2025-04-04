# app/models.py
from app import db
from datetime import datetime
from flask_login import UserMixin
from app.utils import password_checker

class User(db.Model, UserMixin):
    __tablename__ = 'users'
    user_id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(50), unique=True, nullable=False)
    email = db.Column(db.String(255), unique=True, nullable=False)
    password_hash = db.Column(db.String(255), nullable=False)
    role = db.Column(db.String(20), default='customer', nullable=False)
    created_at = db.Column(db.DateTime(timezone=True), default=datetime.utcnow)

    orders = db.relationship('Order', backref='user', lazy=True)

    def get_id(self):
        return str(self.user_id)

    def check_password(self, raw_password):
        return password_checker(self.password_hash, raw_password)

    def __repr__(self):
        return f'<User {self.username}>'

class Item(db.Model):
    __tablename__ = 'items'
    item_id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(100), nullable=False)
    description = db.Column(db.Text)
    price = db.Column(db.Numeric(10, 2), nullable=False)
    stock = db.Column(db.Integer, default=0, nullable=False)
    created_at = db.Column(db.DateTime(timezone=True), default=datetime.utcnow)
    image = db.Column(db.String(100), nullable=True)

    order_items = db.relationship('OrderItem', backref='item', lazy=True)

    def __repr__(self):
        return f'<Item {self.name}>'

    def to_dict(self):
        return {
            "item_id": self.item_id,
            "name": self.name,
            "description": self.description,
            "price": str(self.price),  # Convert Decimal to string if necessary
            "stock": self.stock,
            "image": self.image,
            "created_at": self.created_at
            # include any other fields you need
        }

class Order(db.Model):
    __tablename__ = 'orders'
    order_id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.user_id'), nullable=False)
    total = db.Column(db.Numeric(10, 2), nullable=False)
    order_date = db.Column(db.DateTime(timezone=True), default=datetime.utcnow)
    status = db.Column(db.String(20), default='pending')

    order_items = db.relationship('OrderItem', backref='order', lazy=True)

    def __repr__(self):
        return f'<Order #{self.order_id} User:{self.user_id}>'

class OrderItem(db.Model):
    __tablename__ = 'order_items'
    order_item_id = db.Column(db.Integer, primary_key=True)
    order_id = db.Column(db.Integer, db.ForeignKey('orders.order_id'), nullable=False)
    item_id = db.Column(db.Integer, db.ForeignKey('items.item_id'), nullable=False)
    quantity = db.Column(db.Integer, nullable=False)
    item_price = db.Column(db.Numeric(10, 2), nullable=False)

    def __repr__(self):
        return f'<OrderItem {self.order_item_id} (Order:{self.order_id} / Item:{self.item_id})>'
