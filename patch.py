import sys
f=r'd:\Glossy_Templetes\resources\views\components\layout\add-customer-modal.blade.php'
with open(f, 'r', encoding='utf-8') as file:
    c = file.read()
c = c.replace('p-8 space-y-12', 'p-4 space-y-4')
c = c.replace('gap-8', 'gap-4')
c = c.replace('pb-4 mb-8', 'pb-2 mb-3')
c = c.replace('py-3', 'py-2')
c = c.replace('min-h-[3.5rem]', 'min-h-[2.5rem]')
c = c.replace('min-h-[4rem]', 'min-h-[2.5rem]')
c = c.replace('p-8 bg-muted/20', 'p-4 bg-muted/20 flex')
c = c.replace('px-10 py-6', 'px-6 py-2')
c = c.replace('pb-4 mb-6', 'pb-2 mb-3')
c = c.replace('p-6 border-b', 'p-4 border-b')
c = c.replace('space-y-12', 'space-y-4')
c = c.replace('max-h-[75vh]', 'max-h-[85vh]')

with open(f, 'w', encoding='utf-8') as file:
    file.write(c)
