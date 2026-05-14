from playwright.sync_api import sync_playwright
import time

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page()
        page.on("console", lambda msg: print(f"Browser console: {msg.text}"))
        
        print("Navigating to page...")
        response = page.goto("http://127.0.0.1:8000/customers/10")
        if not response or not response.ok:
            print(f"Failed to load page: {response.status if response else 'Unknown error'}")
            return
            
        time.sleep(2)
        
        print("Clicking cart icon...")
        # Find the cart button based on the icon name or text
        cart_button = page.locator("button", has_text="Cart").first
        
        if cart_button.count() > 0:
            cart_button.click()
            time.sleep(1)
            page.screenshot(path="cart_click.png")
            print("Cart clicked, screenshot taken.")
        else:
            print("Cart button not found!")
            
        browser.close()

run()
