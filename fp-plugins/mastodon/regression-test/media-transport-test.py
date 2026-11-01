"""Loopback-only production transport tests; requires Python 3 and PHP with cURL."""
import argparse
import http.server
import pathlib
import subprocess
import threading

class Handler(http.server.BaseHTTPRequestHandler):
	protocol_version = "HTTP/1.1"
	def log_message(self, *args):
		pass
	def do_GET(self):
		if self.path in ("/redirect", "/loop"):
			self.send_response(302)
			self.send_header("Location", "/exact" if self.path == "/redirect" else "/loop")
			self.send_header("Content-Length", "0")
			self.end_headers()
			return
		self.send_response(200)
		self.send_header("Connection", "close")
		if self.path == "/chunked":
			self.send_header("Transfer-Encoding", "chunked")
			payload = b"9\r\n123456789\r\n0\r\n\r\n"
		else:
			payload = b"123456789" if self.path == "/large" else b"12345678"
			self.send_header("Content-Length", "16" if self.path == "/truncated" else str(len(payload)))
		self.end_headers()
		try:
			self.wfile.write(payload)
		except (BrokenPipeError, ConnectionResetError, ConnectionAbortedError):
			pass
		self.close_connection = True

def main():
	parser = argparse.ArgumentParser()
	parser.add_argument("--php", required=True)
	parser.add_argument("--extension-dir", required=True)
	args = parser.parse_args()
	root = pathlib.Path(__file__).resolve().parents[3]
	with http.server.ThreadingHTTPServer(("127.0.0.1", 0), Handler) as server:
		worker = threading.Thread(target=server.serve_forever, daemon=True)
		worker.start()
		try:
			return subprocess.call([args.php, "-n", "-d", "extension_dir=" + args.extension_dir,
				"-d", "extension=curl", str(pathlib.Path(__file__).with_suffix('.ph_')),
				"http://127.0.0.1:" + str(server.server_port)], cwd=root)
		finally:
			server.shutdown()
if __name__ == "__main__":
	raise SystemExit(main())
