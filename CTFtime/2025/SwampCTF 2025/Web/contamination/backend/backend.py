from flask import Flask, jsonify, request
import os
import logging

app = Flask(__name__)

app.config['DEBUG'] = os.getenv('DEBUG', 'False')
app.config['LOG_LEVEL'] = os.getenv('LOG_LEVEL', 'warning')


@app.route('/api', methods=['POST'])
def api():
    param = request.args.get('action')
    app.logger.info(f"Received param: {param}")

    if param == 'getFlag':
        try:
            data = request.get_json()
            app.logger.info(f"Received JSON data: {data}")
            return jsonify(message="Prased JSON successfully")
        except Exception as e:
            app.logger.error(f"Error parsing JSON: {e}")
            debug_data = {
                'headers': dict(request.headers),
                'method': request.method,
                'url': request.url,
                'env_vars': {key: value for key, value in os.environ.items()}
            }
            return jsonify(message="Something broke!!", debug_data=debug_data)

    if param == 'getInfo':
        debug_status = app.config['DEBUG']
        log_level = app.config['LOG_LEVEL']
        return jsonify(message="Info retrieved successfully!", debug=debug_status, log_level=log_level)

    return jsonify(message="Invalid action parameter!", param=param)


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)