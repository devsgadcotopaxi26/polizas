import pyhanko.stamp as s
print("StaticStampStyle attrs:", [x for x in dir(s.StaticStampStyle) if not x.startswith('_')])
print("TextStampStyle attrs:", [x for x in dir(s.TextStampStyle) if not x.startswith('_')])
