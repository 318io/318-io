foo:
	rm -rf *~

backup:
	rm -rf dump/318_expo
	mongodump -d '318_expo'

restore:
	mongorestore -d '318_expo' dump/318_expo/

cleandb:
	mongo 318_expo --eval 'db.dropDatabase()'

cleanfile:
	rm -rf public/images/stickers/*
	
forever:
	forever start -a -l forever.log -o out.log -e err.log bin/www-mongo
	