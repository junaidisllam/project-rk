import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export interface CartItem {
    id: number;
    name: string;
    writer: { name: string };
    cover_photo: string | null;
    price: number;
    original_price: number | null;
    slug: string;
    quantity: number;
    selected: boolean;
}

export const useCartStore = defineStore('cart', () => {
    const items = ref<CartItem[]>([]);

    // Load cart from localStorage on initialization
    const loadCart = () => {
        const storedCart = localStorage.getItem('cart');
        if (storedCart) {
            items.value = JSON.parse(storedCart);
        }
    };

    // Save cart to localStorage whenever it changes
    const saveCart = () => {
        localStorage.setItem('cart', JSON.stringify(items.value));
    };

    // Initialize cart when store is created
    loadCart();

    const add = (book: Omit<CartItem, 'quantity' | 'selected'>) => {
        const existingItem = items.value.find(item => item.id === book.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            items.value.push({ ...book, quantity: 1, selected: true });
        }
        saveCart();
    };

    const remove = (id: number) => {
        items.value = items.value.filter(item => item.id !== id);
        saveCart();
    };

    const incrementQuantity = (id: number) => {
        const item = items.value.find(item => item.id === id);
        if (item) {
            item.quantity++;
            saveCart();
        }
    };

    const decrementQuantity = (id: number) => {
        const item = items.value.find(item => item.id === id);
        if (item && item.quantity > 1) {
            item.quantity--;
            saveCart();
        }
    };

    const toggleSelectedItem = (id: number) => {
        const item = items.value.find(item => item.id === id);
        if (item) {
            item.selected = !item.selected;
            saveCart();
        }
    };

    const toggleAllSelected = (selected: boolean) => {
        items.value.forEach(item => (item.selected = selected));
        saveCart();
    };

    const clearCart = () => {
        items.value = [];
        saveCart();
    };

    const cartCount = computed(() => items.value.reduce((acc, item) => acc + item.quantity, 0));
    const selectedItems = computed(() => items.value.filter(item => item.selected));

    const subtotal = computed(() =>
        selectedItems.value.reduce((acc, item) => acc + item.price * item.quantity, 0)
    );

    const totalSavings = computed(() =>
        selectedItems.value.reduce(
            (acc, item) =>
                item.original_price
                    ? acc + (item.original_price - item.price) * item.quantity
                    : acc,
            0
        )
    );

    const allSelected = computed(() =>
        items.value.length > 0 && items.value.every(item => item.selected)
    );

    return {
        items,
        add,
        remove,
        incrementQuantity,
        decrementQuantity,
        toggleSelectedItem,
        toggleAllSelected,
        clearCart,
        cartCount,
        selectedItems,
        subtotal,
        totalSavings,
        allSelected,
    };
});
