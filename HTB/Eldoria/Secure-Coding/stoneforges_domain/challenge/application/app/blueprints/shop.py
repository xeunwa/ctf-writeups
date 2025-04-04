# app/blueprints/shop.py
from flask import Blueprint, render_template, redirect, url_for, request, make_response
from flask_login import login_required, current_user, login_user, logout_user
from app.models import Item, Order, User, OrderItem
from app import db
from app.utils import password_hasher

shop = Blueprint('shop', __name__)

@shop.route('/')
def index():
    items = Item.query.limit(3).all()
    return render_template('index.html', items=items)


@shop.route('/about')
def about():
    return render_template('about.html')

@shop.route('/register', methods=['GET', 'POST'])
def register():
    # Redirect to home if already logged in
    if current_user.is_authenticated:
        return redirect(url_for('shop.index'))

    if request.method == 'POST':
        username = request.form.get('username')
        email = request.form.get('email')
        password = request.form.get('password')

        # Check if email or username already in use
        if User.query.filter_by(email=email).first():
            return render_template('register.html', error='Email already in use')
        if User.query.filter_by(username=username).first():
            return render_template('register.html', error='Username already in use')

        # Create a new user and hash the password
        user = User(
            username=username,
            email=email,
            password_hash=password_hasher(password)  # from utils
        )
        db.session.add(user)
        db.session.commit()

        return redirect(url_for('shop.login'))

    return render_template('register.html')

@shop.route('/login', methods=['GET', 'POST'])
def login():
    # Redirect if already logged in
    if current_user.is_authenticated:
        return redirect(url_for('shop.index'))

    if request.method == 'POST':
        email = request.form.get('email')
        password = request.form.get('password')

        user = User.query.filter_by(email=email).first()
        if user and user.check_password(password):
            login_user(user)
            return redirect(url_for('shop.index'))
        return render_template('login.html', error='Invalid credentials')

    return render_template('login.html')

@shop.route('/logout')
@login_required
def logout():
    logout_user()
    return redirect(url_for('shop.index'))

@shop.route('/list')
@login_required
def list_items():
    items = Item.query.all()
    items_dict = [item.to_dict() for item in items]
    return render_template('list.html', items=items_dict)

@shop.route('/buy/<int:item_id>', methods=['POST'])
@login_required
def buy_item(item_id):
    item = Item.query.get_or_404(item_id)

    quantity = request.form.get('quantity', '1')
    try:
        quantity = int(quantity)
    except ValueError:
        return redirect(url_for('shop.list_items'))

    if quantity < 1:
        return make_response('Invalid quantity', 400)
    if quantity > item.stock:
        return make_response('Not enough stock', 400)

    total_price = item.price * quantity

    new_order = Order(
        user_id=current_user.user_id,
        total=total_price
    )
    db.session.add(new_order)
    db.session.commit()  # We need an order_id to link order_items

    # Create an OrderItem linked to the new Order
    order_item = OrderItem(
        order_id=new_order.order_id,
        item_id=item_id,
        quantity=quantity,
        item_price=item.price
    )
    db.session.add(order_item)

    # Update the item stock
    item.stock -= quantity
    db.session.commit()

    # Redirect to the user's dashboard or back to list, whichever you prefer
    return redirect(url_for('shop.user_dashboard'))


@shop.route('/admin')
@login_required
def admin_dashboard():
    # Only allow admin role
    if current_user.role != 'admin':
        return redirect(url_for('shop.index'))

    orders = Order.query.order_by(Order.order_date.desc()).all()
    return render_template('admin_dashboard.html', orders=orders)


@shop.route('/admin/approve/<int:order_id>', methods=['POST'])
@login_required
def approve_order(order_id):
    if current_user.role != 'admin':
        return redirect(url_for('shop.index'))

    order = Order.query.get_or_404(order_id)

    order.status = 'approved'
    db.session.commit()

    return redirect(url_for('shop.admin_dashboard'))

@shop.route('/admin/deny/<int:order_id>', methods=['POST'])
@login_required
def deny_order(order_id):
    if current_user.role != 'admin':
        return redirect(url_for('shop.index'))

    order = Order.query.get_or_404(order_id)

    order.status = 'denied'
    db.session.commit()

    return redirect(url_for('shop.admin_dashboard'))


@shop.route('/dashboard')
@login_required
def user_dashboard():
    orders = Order.query.filter_by(user_id=current_user.user_id).order_by(Order.order_date.desc()).all()
    return render_template('user_dashboard.html', orders=orders)
