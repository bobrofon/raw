__author__ = 'bobrofon'

import asyncore
import socket
import re

WAF_HOST = "127.0.0.1"
WAF_LISTEN = 5
WAF_BUF_SIZE = 16 * 1024


class TbWafSession:
    io_handler = None
    oi_handler = None
    io_handler_close = None
    oi_handler_close = None
    signatures = []

    def __init__(self, sock, dst_host, dst_port, signatures):
        self.signatures = signatures
        self.io_handler = asyncore.dispatcher_with_send(sock)
        self.oi_handler = asyncore.dispatcher_with_send()
        self.oi_handler.create_socket(socket.AF_INET, socket.SOCK_STREAM)
        self.oi_handler.connect((dst_host, dst_port))
        self.io_handler.handle_read = self.io_read
        self.oi_handler.handle_read = self.oi_read
        self.io_handler_close = self.io_handler.close
        self.io_handler.close = self.close
        self.oi_handler_close = self.oi_handler.close
        self.oi_handler.close = self.close

    def io_read(self):
        if self.io_handler and self.oi_handler:
            data = self.io_handler.recv(WAF_BUF_SIZE)
            if data:
                for sign in self.signatures:
                    if sign.search(data):
                        self.close()
                        return
                self.oi_handler.send(data)

    def oi_read(self):
        if self.io_handler and self.oi_handler:
            data = self.oi_handler.recv(WAF_BUF_SIZE)
            if data:
                self.io_handler.send(data)

    def close(self):
        self.io_handler_close()
        self.oi_handler_close()


class TbWafServer(asyncore.dispatcher):
    dst_host = ""
    dst_port = 0
    signatures = []

    def __init__(self, src_port, dst_port, src_host=WAF_HOST, dst_host=WAF_HOST, signatures=[]):
        asyncore.dispatcher.__init__(self)
        self.dst_host = dst_host
        self.dst_port = dst_port
        self.signatures = signatures
        self.create_socket(socket.AF_INET, socket.SOCK_STREAM)
        self.set_reuse_addr()
        self.bind((src_host, src_port))
        self.listen(WAF_LISTEN)

    def handle_accept(self):
        pair = self.accept()
        if pair is not None:
            sock, _ = pair
            _ = TbWafSession(sock, self.dst_host, self.dst_port, self.signatures)

    def add_signature(self, sign, ignore_case=re.IGNORECASE):
        self.signatures.append(re.compile(sign, ignore_case))

    @staticmethod
    def run():
        asyncore.loop()
