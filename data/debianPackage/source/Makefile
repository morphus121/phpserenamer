DESTDIR=
BINDIR=/usr/lib

install:
	mkdir -p $(DESTDIR)$(BINDIR)/phpserenamer
	cp -R phpserenamer/* $(DESTDIR)$(BINDIR)/phpserenamer
	cp -R etc/ $(DESTDIR)/etc
	cp -R usr/ $(DESTDIR)/usr
#	install -d -m 0777 -o root -g root $(DESTDIR)/$(BINDIR)/phpserenamer
#	install    -m 0777 -o root -g root phpserenamer/ $(DESTDIR)/$(BINDIR)/phpserenamer

clean:
