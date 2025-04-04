from flask import Flask, send_from_directory, abort, request
import os

app = Flask(
    __name__, 
    static_folder="/app/frontend/browser"
)

@app.route("/", defaults={"path": "index.html"})
@app.route("/<path:path>")
def serve_files(path):
    try:
        return send_from_directory(app.static_folder, path)
    except:
        referer = request.headers.get("Referer", "")
        if not referer or not (referer.startswith("http://127.0.0.1:5000/") or referer.startswith("http://localhost:5000/")):
            print(referer)
            abort(403, description="Forbidden: Accessing files directly is not allowed... You didn't think it'd be that easy did you.")

        return send_from_directory("/app/", path)

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=False)
