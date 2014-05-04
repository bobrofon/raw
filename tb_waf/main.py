__author__ = 'bobrofon'

from tb_waf import TbWafServer

if __name__ == "__main__":
    app = TbWafServer(5555, 7777, "0.0.0.0")
    app.add_signature("ololo")
    app.add_signature("trololo")
    app.run()